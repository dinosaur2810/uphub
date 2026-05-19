pipeline {
    agent any

    environment {
        COMPOSE_PROJECT_NAME = 'uphub-ci'
        APP_PORT = '8082'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Validate') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'test -f Dockerfile && test -f docker-compose.yml && test -f composer.json'
                    } else {
                        bat 'if not exist Dockerfile exit /b 1'
                        bat 'if not exist docker-compose.yml exit /b 1'
                        bat 'if not exist composer.json exit /b 1'
                    }
                }
            }
        }

        stage('Docker Build') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'docker compose -f docker-compose.yml -f docker-compose.ci.yml build --no-cache'
                    } else {
                        bat 'docker compose -f docker-compose.yml -f docker-compose.ci.yml build --no-cache'
                    }
                }
            }
        }

        stage('Integration Test') {
            steps {
                script {
                    if (isUnix()) {
                        sh '''
                            docker compose -f docker-compose.yml -f docker-compose.ci.yml down -v || true
                            docker compose -f docker-compose.yml -f docker-compose.ci.yml up -d --wait
                            docker compose -f docker-compose.yml -f docker-compose.ci.yml ps
                            curl -f http://localhost:${APP_PORT}/UpHub/index.php
                        '''
                    } else {
                        bat '''
                            docker compose down 2>nul
                            docker compose -f docker-compose.yml -f docker-compose.ci.yml down -v --remove-orphans
                            docker compose -f docker-compose.yml -f docker-compose.ci.yml up -d --wait
                            docker compose -f docker-compose.yml -f docker-compose.ci.yml ps
                            curl -f http://localhost:%APP_PORT%/UpHub/index.php
                        '''
                    }
                }
            }
        }
    }

    post {
        always {
            script {
                if (isUnix()) {
                    sh 'docker compose -f docker-compose.yml -f docker-compose.ci.yml down -v || true'
                } else {
                    bat 'docker compose -f docker-compose.yml -f docker-compose.ci.yml down -v'
                }
            }
        }
        success {
            echo 'UpHub build completed successfully.'
        }
        failure {
            echo 'UpHub build failed. Check Docker and database init logs.'
        }
    }
}
