<?php
namespace C8EEE\Parcelninja\Gateway\Parcelninja;

interface EndpointInterface {
	public function makeBody($parameters = []);

	public function makeRequestHeaders($parameters = []);
}
