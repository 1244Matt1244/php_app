#!/usr/bin/env bash

# Configuration
WEB_SERVICE="nginx-web"
TIMESTAMP="$(date '+%Y-%m-%d %H:%M:%S')"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Enhanced logging
log() {
  local level=$1
  local message=$2
  case $level in
    "info") echo -e "${GREEN}[INFO]${NC} $TIMESTAMP - $message" ;;
    "warn") echo -e "${YELLOW}[WARN]${NC} $TIMESTAMP - $message" ;;
    "error") echo -e "${RED}[ERROR]${NC} $TIMESTAMP - $message" >&2 ;;
  esac
}

# Dependency checks
check_dependencies() {
  local missing=()
  
  if ! command -v docker &> /dev/null; then
    missing+=("Docker")
  fi

  if ! command -v docker-compose &> /dev/null; then
    missing+=("Docker Compose")
  fi

  if [[ ${#missing[@]} -gt 0 ]]; then
    log error "Missing dependencies: ${missing[*]}"
    exit 1
  fi

  if ! docker info &> /dev/null; then
    log error "Docker daemon is not running"
    exit 1
  fi
}

# Service management functions
start_services() {
  log info "Building and starting services..."
  if docker-compose up -d --build; then
    log info "Services started successfully"
  else
    log error "Failed to start services"
    exit 1
  fi
}

stop_services() {
  local remove_volumes=false
  log info "Stopping services..."
  
  read -p $'\e[33mRemove volumes? (y/N): \e[0m' -n 1 confirm
  echo
  [[ "$confirm" == [yY] ]] && remove_volumes=true

  if $remove_volumes; then
    log warn "Removing services and volumes..."
    docker-compose down -v || {
      log error "Failed to remove services and volumes"
      exit 1
    }
  else
    docker-compose down || {
      log error "Failed to stop services"
      exit 1
    }
  fi
}

service_status() {
  log info "Current service status:"
  docker-compose ps
}

view_logs() {
  local service="${1:-$WEB_SERVICE}"
  log info "Tailing logs for $service..."
  docker-compose logs -f "$service"
}

restart_services() {
  log info "Restarting services..."
  docker-compose down && docker-compose up -d --build || {
    log error "Failed to restart services"
    exit 1
  }
}

# Main execution
main() {
  check_dependencies

  case "$1" in
    start)
      start_services
      ;;
    stop)
      stop_services
      ;;
    status)
      service_status
      ;;
    logs)
      view_logs "$2"
      ;;
    logs-all)
      log info "Tailing all service logs..."
      docker-compose logs -f
      ;;
    restart)
      restart_services
      ;;
    *)
      echo -e "${YELLOW}Usage:${NC} $0 {start|stop|status|logs [service]|logs-all|restart}"
      exit 1
      ;;
  esac
}

main "$@"
