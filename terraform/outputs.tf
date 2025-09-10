output "vm_external_ip" {
  description = "O endereço de IP externo da VM do servidor web."
  value       = google_compute_instance.web_server.network_interface[0].access_config[0].nat_ip
}
