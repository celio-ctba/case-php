variable "gcp_project_id" {
  description = "O ID do seu projeto no GCP."
  type        = string
}

variable "gcp_region" {
  description = "A região do GCP onde os recursos serão criados."
  type        = string
  default     = "us-central1"
}

variable "gcp_zone" {
  description = "A zona do GCP onde a VM será criada."
  type        = string
  default     = "us-central1-a"
}
