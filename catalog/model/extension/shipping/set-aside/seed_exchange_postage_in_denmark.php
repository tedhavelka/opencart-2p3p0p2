<?php
class ModelExtensionShippingSeedExchangePostageInDenmark extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/seed_exchange_postage_in_denmark');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('seed_exchange_postage_in_denmark_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('seed_exchange_postage_in_denmark_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$quote_data = array();

			$quote_data['seed_exchange_postage_in_denmark'] = array(
				'code'         => 'seed_exchange_postage_in_denmark.seed_exchange_postage_in_denmark',
				'title'        => $this->language->get('text_description'),
				'cost'         => $this->config->get('seed_exchange_postage_in_denmark_cost'),
				'tax_class_id' => $this->config->get('seed_exchange_postage_in_denmark_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($this->config->get('seed_exchange_postage_in_denmark_cost'), $this->config->get('seed_exchange_postage_in_denmark_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'seed_exchange_postage_in_denmark',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('seed_exchange_postage_in_denmark_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}
