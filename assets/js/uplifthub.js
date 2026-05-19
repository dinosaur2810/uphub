/**
 * UpLiftHub — sidebar, validation helpers, modals
 */
(function () {
  'use strict';

  function initSidebar() {
    var sidebar = document.getElementById('dashboardSidebar');
    var toggle = document.getElementById('sidebarToggle');
    var btnClose = document.getElementById('sidebarClose');
    var backdrop = document.getElementById('sidebarBackdrop');

    if (!sidebar || !toggle) return;

    function openSidebar() {
      sidebar.classList.add('is-open');
      if (backdrop) {
        backdrop.hidden = false;
        backdrop.style.opacity = '1';
      }
      document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
      sidebar.classList.remove('is-open');
      if (backdrop) {
        backdrop.style.opacity = '0';
        setTimeout(function() { if (!sidebar.classList.contains('is-open')) backdrop.hidden = true; }, 300);
      }
      document.body.style.overflow = '';
    }

    toggle.addEventListener('click', openSidebar);
    if (btnClose) btnClose.addEventListener('click', closeSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
  }

  function initLiveValidation(form) {
    if (!form) return;
    var email = form.querySelector('input[type="email"]');
    var pass = form.querySelector('input[name="password"]');
    var pass2 = form.querySelector('input[name="password_confirm"]');

    function validateEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
    function validatePassword(v) { return v.length >= 8 && /[A-Za-z]/.test(v) && /[0-9]/.test(v); }

    function showFieldError(el, msg) {
      if (!el) return;
      var err = el.parentNode.querySelector('.invalid-feedback');
      if (!err) {
        err = document.createElement('div');
        err.className = 'invalid-feedback';
        el.parentNode.appendChild(err);
      }
      if (msg) {
        el.classList.add('is-invalid');
        err.textContent = msg;
        err.style.display = 'block';
      } else {
        el.classList.remove('is-invalid');
        err.style.display = 'none';
      }
    }

    if (email) {
      email.addEventListener('blur', function() {
        if (email.value.trim() && !validateEmail(email.value.trim())) {
          showFieldError(email, 'Enter a valid email address.');
        } else {
          showFieldError(email, '');
        }
      });
    }

    form.addEventListener('submit', function (e) {
      var ok = true;
      if (email && email.value.trim() && !validateEmail(email.value.trim())) {
        showFieldError(email, 'Enter a valid email address.');
        ok = false;
      }
      if (pass && pass.value && !validatePassword(pass.value)) {
        showFieldError(pass, 'Min 8 characters with letters and numbers.');
        ok = false;
      }
      if (pass2 && pass && pass2.value !== pass.value) {
        showFieldError(pass2, 'Passwords do not match.');
        ok = false;
      }
      if (!ok) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }

  function initImagePreview() {
    document.querySelectorAll('[data-preview-target]').forEach(function (input) {
      var targetId = input.getAttribute('data-preview-target');
      var target = targetId ? document.getElementById(targetId) : null;
      if (!target) return;
      input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file || !file.type.match(/^image\//)) {
          target.removeAttribute('src');
          target.classList.add('d-none');
          return;
        }
        var url = URL.createObjectURL(file);
        target.src = url;
        target.classList.remove('d-none');
      });
    });
  }

  function initJobSearch() {
    var input = document.getElementById('jobSearchInput');
    if (!input) return;
    var table = document.getElementById('jobTableBody');
    var grid = document.getElementById('jobCardGrid');
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      if (table) {
        table.querySelectorAll('tr[data-search]').forEach(function (row) {
          var hay = (row.getAttribute('data-search') || '').toLowerCase();
          row.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
        });
      }
      if (grid) {
        grid.querySelectorAll('.job-card-col[data-search]').forEach(function (col) {
          var hay = (col.getAttribute('data-search') || '').toLowerCase();
          col.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
        });
      }
    });
  }

  function initUserSearch() {
    var input = document.getElementById('userSearchInput');
    var table = document.getElementById('userTableBody');
    if (!input || !table) return;
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      table.querySelectorAll('tr[data-search]').forEach(function (row) {
        var hay = (row.getAttribute('data-search') || '').toLowerCase();
        row.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
      });
    });
  }

  function initAidFilter() {
    var sel = document.getElementById('aidStatusFilter');
    var container = document.getElementById('aidCards');
    if (!sel || !container) return;
    sel.addEventListener('change', function () {
      var v = sel.value;
      container.querySelectorAll('[data-status]').forEach(function (card) {
        var st = card.getAttribute('data-status');
        card.closest('.col-12, .col-md-6, .col-lg-4').style.display =
          v === 'all' || st === v ? '' : 'none';
      });
    });
  }

  function initListingTypeForm() {
    var sel = document.getElementById('listingType');
    if (!sel) return;
    function sync() {
      var v = sel.value;
      document.querySelectorAll('[data-listing-fields]').forEach(function (el) {
        el.hidden = el.getAttribute('data-listing-fields') !== v;
      });
    }
    sel.addEventListener('change', sync);
    sync();
  }

  document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    document.querySelectorAll('form[data-live-validate="true"]').forEach(initLiveValidation);
    initImagePreview();
    initJobSearch();
    initUserSearch();
    initAidFilter();
    initListingTypeForm();
  });
})();

/* Public pages — fetch APIs, render cards, filters, map placeholder and apply modal handling */
(function () {
  'use strict';

  function ajaxJson(url) {
    return fetch(url, {cache: 'no-store'}).then(function (r) { if (!r.ok) throw new Error('Network'); return r.json(); });
  }

  /* Apply modal handling */
  function initApplyModal() {
    var modalEl = document.getElementById('applyModal');
    if (!modalEl) return;
    var bsModal = new bootstrap.Modal(modalEl);
    var form = document.getElementById('applyForm');
    var submit = document.getElementById('applyFormSubmit');
    var alertBox = document.getElementById('applyFormAlert');

    document.addEventListener('click', function (e) {
      var t = e.target.closest('[data-apply-item]');
      if (!t) return;
      var type = t.getAttribute('data-apply-type') || t.getAttribute('data-item-type') || 'job';
      var id = t.getAttribute('data-apply-id') || t.getAttribute('data-item-id') || '';
      document.getElementById('apply_item_type').value = type;
      document.getElementById('apply_item_id').value = id;
      if (alertBox) alertBox.classList.add('d-none');
      bsModal.show();
    });

    submit.addEventListener('click', function () {
      var data = new FormData(form);
      var endpoint = window.UPLIFT && window.UPLIFT.applyApi ? window.UPLIFT.applyApi : 'api/apply.php';
      fetch(endpoint, {method: 'POST', body: data}).then(function (r) { return r.json(); }).then(function (j) {
        if (j && j.success) {
          if (alertBox) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = j.message || 'Application sent.';
            alertBox.classList.remove('d-none');
          }
          setTimeout(function () { bsModal.hide(); }, 900);
        } else {
          if (alertBox) {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = (j && j.error) ? j.error : 'Could not submit.';
            alertBox.classList.remove('d-none');
          }
        }
      }).catch(function () {
        if (alertBox) {
          alertBox.className = 'alert alert-danger';
          alertBox.textContent = 'Network error.';
          alertBox.classList.remove('d-none');
        }
      });
    });
  }

  /* Render helpers */
  function createCard(colClass, innerHtml, attrs) {
    var col = document.createElement('div');
    col.className = colClass + ' job-card-col';
    if (attrs && attrs['data-search']) col.setAttribute('data-search', attrs['data-search']);
    col.innerHTML = innerHtml;
    return col;
  }

  /* Jobs */
  function initJobs() {
    var api = window.UPLIFT && window.UPLIFT.jobsApi ? window.UPLIFT.jobsApi : 'api/jobs.php';
    var grid = document.getElementById('jobsGrid');
    var search = document.getElementById('jobsSearch');
    var loc = document.getElementById('jobsLocation');
    var cat = document.getElementById('jobsCategory');
    var typeSel = document.getElementById('jobsType');
    if (!grid) return;
    ajaxJson(api).then(function (res) {
      var data = (res && res.data) ? res.data : [];
      var locations = new Set();
      var categories = new Set();
      grid.innerHTML = '';
      data.forEach(function (j) {
        locations.add(j.location);
        categories.add(j.category);
        var hay = [j.title, j.company, j.location, j.short, j.category].join(' ');
        var html = '<div class="card h-100">'
          + '<div class="card-body d-flex flex-column">'
          + '<h5 class="card-title">' + (j.title) + '</h5>'
          + '<h6 class="card-subtitle mb-2 text-muted">' + (j.company) + ' — ' + (j.location) + '</h6>'
          + '<p class="card-text flex-grow-1">' + (j.short) + '</p>'
          + '<p class="small mb-2"><strong>Type:</strong> ' + (j.type) + ' • <strong>Salary:</strong> ' + (j.salary) + '</p>'
          + '<div class="mt-3 d-flex justify-content-between align-items-center">'
          + '<div class="small text-muted">' + (j.requirements.join(', ')) + '</div>'
          + '<div><button class="btn btn-sm btn-outline-uplift" data-apply-item data-apply-type="job" data-apply-id="' + j.id + '">Apply</button></div>'
          + '</div></div></div>';
        var col = createCard('col-12 col-md-6 col-lg-4', html, {'data-search': hay});
        grid.appendChild(col);
      });
      // populate filters
      if (loc) {
        locations.forEach(function (v) { var o = document.createElement('option'); o.value = v; o.textContent = v; loc.appendChild(o); });
      }
      if (cat) {
        categories.forEach(function (v) { var o = document.createElement('option'); o.value = v; o.textContent = v; cat.appendChild(o); });
      }

      function filterJobs() {
        var q = (search && search.value || '').trim().toLowerCase();
        var locv = (loc && loc.value) || '';
        var catv = (cat && cat.value) || '';
        var typev = (typeSel && typeSel.value) || '';
        grid.querySelectorAll('.job-card-col[data-search]').forEach(function (col) {
          var hay = (col.getAttribute('data-search')||'').toLowerCase();
          var ok = (!q || hay.indexOf(q) !== -1);
          if (ok && locv) ok = hay.indexOf(locv.toLowerCase()) !== -1;
          if (ok && catv) ok = hay.indexOf(catv.toLowerCase()) !== -1;
          if (ok && typev) ok = hay.indexOf(typev.toLowerCase()) !== -1;
          col.style.display = ok ? '' : 'none';
        });
      }

      [search, loc, cat, typeSel].forEach(function (el) { if (el) el.addEventListener('input', filterJobs); el && el.addEventListener('change', filterJobs); });
    }).catch(function () {
      grid.innerHTML = '<div class="col-12"><div class="alert alert-warning">Could not load jobs.</div></div>';
    });
  }

  /* Aid */
  function initAid() {
    var api = window.UPLIFT && window.UPLIFT.aidApi ? window.UPLIFT.aidApi : 'api/aid.php';
    var grid = document.getElementById('aidGrid');
    var search = document.getElementById('aidSearch');
    var type = document.getElementById('aidType');
    var deadline = document.getElementById('aidDeadline');
    if (!grid) return;
    ajaxJson(api).then(function (res) {
      var data = (res && res.data) ? res.data : [];
      grid.innerHTML = '';
      data.forEach(function (a) {
        var hay = [a.title, a.provider, a.type, a.short].join(' ');
        var html = '<div class="card h-100">'
          + '<div class="card-body d-flex flex-column">'
          + '<h5 class="card-title">' + a.title + '</h5>'
          + '<h6 class="card-subtitle mb-2 text-muted">' + a.provider + '</h6>'
          + '<p class="card-text flex-grow-1">' + a.short + '</p>'
          + '<p class="small mb-2"><strong>Type:</strong> ' + a.type + ' • <strong>Deadline:</strong> ' + a.deadline + '</p>'
          + '<div class="mt-3 d-flex justify-content-end">'
          + '<button class="btn btn-sm btn-outline-uplift" data-apply-item data-apply-type="aid" data-apply-id="' + a.id + '">Apply</button>'
          + '</div></div></div>';
        var col = createCard('col-12 col-md-6 col-lg-4', html, {'data-search': hay});
        grid.appendChild(col);
      });

      function filterAid() {
        var q = (search && search.value || '').trim().toLowerCase();
        var typev = (type && type.value) || '';
        var dv = (deadline && deadline.value) || '';
        grid.querySelectorAll('.job-card-col[data-search]').forEach(function (col) {
          var hay = (col.getAttribute('data-search')||'').toLowerCase();
          var ok = (!q || hay.indexOf(q) !== -1);
          if (ok && typev) ok = hay.indexOf(typev.toLowerCase()) !== -1;
          // simple deadline filter: 'open' shows all, 'closing_soon' show all for demo
          col.style.display = ok ? '' : 'none';
        });
      }
      [search, type, deadline].forEach(function (el) { if (el) el.addEventListener('input', filterAid); el && el.addEventListener('change', filterAid); });
    }).catch(function () {
      grid.innerHTML = '<div class="col-12"><div class="alert alert-warning">Could not load aid programs.</div></div>';
    });
  }

  /* Services + Map placeholder */
  function initServices() {
    var api = window.UPLIFT && window.UPLIFT.servicesApi ? window.UPLIFT.servicesApi : 'api/services.php';
    var grid = document.getElementById('servicesGrid');
    var search = document.getElementById('servicesSearch');
    var cat = document.getElementById('servicesCategory');
    var loc = document.getElementById('servicesLocation');
    var map = document.getElementById('mapPlaceholder');
    if (!grid || !map) return;
    ajaxJson(api).then(function (res) {
      var data = (res && res.data) ? res.data : [];
      grid.innerHTML = '';
      map.innerHTML = '';
      var markers = [];
      data.forEach(function (s) {
        var hay = [s.name, s.provider, s.category, s.location, s.contact].join(' ');
        var html = '<div class="card h-100">'
          + '<div class="card-body d-flex flex-column">'
          + '<h5 class="card-title">' + s.name + '</h5>'
          + '<h6 class="card-subtitle mb-2 text-muted">' + s.provider + ' — ' + s.location + '</h6>'
          + '<p class="card-text flex-grow-1">' + s.short + '</p>'
          + '<p class="small mb-2"><strong>Contact:</strong> ' + s.contact + '</p>'
          + '<div class="mt-3 d-flex justify-content-between align-items-center">'
          + '<div class="small text-muted">' + s.category + '</div>'
          + '<div><button class="btn btn-sm btn-outline-uplift" data-service-id="' + s.id + '">Details</button></div>'
          + '</div></div></div>';
        var col = createCard('col-12 col-md-6', html, {'data-search': hay});
        grid.appendChild(col);
        // add marker stub
        var m = document.createElement('div');
        m.className = 'p-2 mb-2 marker';
        m.style.border = '1px dashed rgba(0,0,0,0.06)';
        m.style.borderRadius = '6px';
        m.style.background = '#fff';
        m.textContent = s.name + ' — ' + s.location;
        m.setAttribute('data-id', s.id);
        map.appendChild(m);
        markers.push(m);
      });

      map.querySelectorAll('.marker').forEach(function (m) { m.style.cursor = 'pointer'; });
      // clicking a card highlights marker
      grid.querySelectorAll('[data-service-id]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-service-id');
          markers.forEach(function (mm) { mm.style.boxShadow = mm.getAttribute('data-id') === id ? '0 0 0 3px rgba(25,118,210,0.12)' : ''; });
          var sel = map.querySelector('[data-id="' + id + '"]'); if (sel) sel.scrollIntoView({behavior:'smooth',block:'center'});
        });
      });

      function filterServices() {
        var q = (search && search.value || '').trim().toLowerCase();
        var catv = (cat && cat.value) || '';
        var locv = (loc && loc.value) || '';
        grid.querySelectorAll('.job-card-col[data-search]').forEach(function (col) {
          var hay = (col.getAttribute('data-search')||'').toLowerCase();
          var ok = (!q || hay.indexOf(q) !== -1);
          if (ok && catv) ok = hay.indexOf(catv.toLowerCase()) !== -1;
          if (ok && locv) ok = hay.indexOf(locv.toLowerCase()) !== -1;
          col.style.display = ok ? '' : 'none';
        });
      }
      [search, cat, loc].forEach(function (el) { if (el) el.addEventListener('input', filterServices); el && el.addEventListener('change', filterServices); });
    }).catch(function () {
      grid.innerHTML = '<div class="col-12"><div class="alert alert-warning">Could not load services.</div></div>';
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initApplyModal();
    initJobs();
    initAid();
    initServices();
  });
})();

/* Home landing: fetch featured items and render compact cards */
(function () {
  'use strict';

  function smallCardHtml(title, meta, short, id, type) {
    return '<div class="card h-100">'
      + '<div class="card-body d-flex flex-column">'
      + '<h6 class="mb-1">' + title + '</h6>'
      + '<div class="small text-muted mb-2">' + meta + '</div>'
      + '<div class="small flex-grow-1">' + short + '</div>'
      + '<div class="mt-3 text-end"><button class="btn btn-sm btn-outline-uplift" data-apply-item data-apply-type="' + type + '" data-apply-id="' + id + '">Apply</button></div>'
      + '</div></div>';
  }

  function initHomeFeatured() {
    var jobsContainer = document.getElementById('featuredJobsGrid');
    var aidContainer = document.getElementById('featuredAidGrid');
    var servicesContainer = document.getElementById('featuredServicesGrid');
    if (!jobsContainer && !aidContainer && !servicesContainer) return;

    if (jobsContainer && window.UPLIFT && window.UPLIFT.jobsApi) {
      fetch(window.UPLIFT.jobsApi).then(function (r) { return r.json(); }).then(function (j) {
        var data = j.data || [];
        data.slice(0,4).forEach(function (it) {
          var col = document.createElement('div'); col.className = 'col-12'; col.innerHTML = smallCardHtml(it.title, it.company + ' • ' + it.location + ' • ' + it.type, it.short, it.id, 'job');
          jobsContainer.appendChild(col);
        });
      }).catch(function () {});
    }

    if (aidContainer && window.UPLIFT && window.UPLIFT.aidApi) {
      fetch(window.UPLIFT.aidApi).then(function (r) { return r.json(); }).then(function (j) {
        var data = j.data || [];
        data.slice(0,4).forEach(function (it) {
          var col = document.createElement('div'); col.className = 'col-12'; col.innerHTML = smallCardHtml(it.title, it.provider + ' • ' + it.type, it.short, it.id, 'aid');
          aidContainer.appendChild(col);
        });
      }).catch(function () {});
    }

    if (servicesContainer && window.UPLIFT && window.UPLIFT.servicesApi) {
      fetch(window.UPLIFT.servicesApi).then(function (r) { return r.json(); }).then(function (j) {
        var data = j.data || [];
        data.slice(0,4).forEach(function (it) {
          var col = document.createElement('div'); col.className = 'col-12'; col.innerHTML = smallCardHtml(it.name, it.provider + ' • ' + it.location, it.short, it.id, 'service');
          servicesContainer.appendChild(col);
        });
      }).catch(function () {});
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initHomeFeatured();
  });
})();


