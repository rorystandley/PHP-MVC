CREATE TABLE `api_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `log_account` varchar(255) DEFAULT NULL,
  `log_http_method` varchar(255) DEFAULT NULL,
  `log_request_path` varchar(255) DEFAULT NULL,
  `log_headers` text,
  `log_input` text,
  `log_result_code` varchar(255) DEFAULT NULL,
  `log_output_content` text,
  PRIMARY KEY (`id`)
)