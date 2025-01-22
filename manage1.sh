#!/bin/bash

# Function to start all services
start() {
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Starting all services..."
  if docker-compose up -d --build; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - All services started successfully."
  else
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Failed to start services."
    exit 1
  fi
}

# Function to stop all services
stop() {
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Stopping all services..."
  
  read -p "Do you also want to remove volumes? This will delete all persisted data! (y/N): " confirm
  if [[ $confirm == "y" || $confirm == "Y" ]]; then
    if docker-compose down --volumes; then
      echo "$(date '+%Y-%m-%d %H:%M:%S') - All services and volumes stopped and removed."
    else
      echo "$(date '+%Y-%m-%d %H:%M:%S') - Failed to stop and remove volumes."
      exit 1
    fi
  else
    if docker-compose down; then
      echo "$(date '+%Y-%m-%d %H:%M:%S') - All services stopped (volumes retained)."
    else
      echo "$(date '+%Y-%m-%d %H:%M:%S') - Failed to stop services."
      exit 1
    fi
  fi
}

# Function to check the status of services
status() {
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Checking service status..."
  docker-compose ps
}

# Function to view the web server logs
logs() {
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Viewing web server logs..."
  docker-compose logs -f nginx-web
}

# Function to view all container logs
logs_all() {
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Viewing all service logs..."
  docker-compose logs -f
}

# Function to restart all services
restart() {
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Restarting all services..."
  if docker-compose down && docker-compose up -d --build; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - All services restarted successfully."
  else
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Failed to restart services."
    exit 1
  fi
}

# Function to check if Docker and Docker Compose are installed
check_dependencies() {
  if ! command -v docker-compose &> /dev/null; then
    echo "docker-compose could not be found. Please install it."
    exit 1
  fi

  if ! command -v docker &> /dev/null; then
    echo "Docker could not be found. Please install it."
    exit 1
  fi

  if ! systemctl is-active --quiet docker; then
    echo "Docker service is not running. Please start Docker."
    exit 1
  fi
}

# Check dependencies
check_dependencies

# Handle script arguments
case "$1" in
  start)
    start
    ;;
  stop)
    stop
    ;;
  status)
    status
    ;;
  logs)
    logs
    ;;
  logs_all)
    logs_all
    ;;
  restart)
    restart
    ;;
  *)
    echo "Usage: ./manage.sh {start|stop|status|logs|logs_all|restart}"
    exit 1
    ;;
esac
