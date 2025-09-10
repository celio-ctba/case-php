terraform {
  required_providers {
    google = {
      source  = "hashicorp/google"
      version = ">= 4.50.0"
    }
  }
}

provider "google" {
  project = var.gcp_project_id
  region  = var.gcp_region
  zone    = var.gcp_zone
}

resource "google_compute_network" "vpc_network" {
  name                    = "php-app-network"
  auto_create_subnetworks = false
}

resource "google_compute_subnetwork" "default" {
  name          = "php-app-subnet"
  ip_cidr_range = "10.0.1.0/24"
  network       = google_compute_network.vpc_network.id
  region        = var.gcp_region
}

resource "google_compute_firewall" "allow_http" {
  name    = "php-app-allow-http"
  network = google_compute_network.vpc_network.name
  allow {
    protocol = "tcp"
    ports    = ["80"]
  }
  source_ranges = ["0.0.0.0/0"]
  target_tags   = ["web-server"]
}

resource "google_compute_firewall" "allow_ssh" {
  name    = "php-app-allow-ssh"
  network = google_compute_network.vpc_network.name
  allow {
    protocol = "tcp"
    ports    = ["22"]
  }
  source_ranges = ["0.0.0.0/0"] # Para produção, restrinja ao seu IP
  target_tags   = ["web-server"]
}

resource "google_compute_instance" "web_server" {
  name         = "php-mysql-vm"
  machine_type = "e2-medium"
  tags         = ["web-server"]

  boot_disk {
    initialize_params {
      image = "debian-cloud/debian-11"
    }
  }

  network_interface {
    network    = google_compute_network.vpc_network.id
    subnetwork = google_compute_subnetwork.default.id
    access_config {
      // Ephemeral IP
    }
  }

  // Script que será executado na inicialização da VM
  metadata_startup_script = file("install_webserver.sh")

  service_account {
    scopes = ["cloud-platform"]
  }
}
