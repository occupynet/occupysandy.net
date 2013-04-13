<?php
/**
 * @property array $sites Only present if the license server supports it.
 */
class Wslm_ProductLicense implements ArrayAccess {
    private $data = array();

    public function __construct($data = null) {
        if ( !empty($data) ) {
            $this->data = (array)$data;
        }
    }

    public function getData() {
        return $this->data;
    }

	public function isValid() {
		return $this->getStatus() == 'valid';
	}

	public function getStatus() {
		$status = $this->get('status');
		if ( $status === null ) {
			$status = 'valid';
		}

		if ( $status === 'valid' ) {
			$expires = $this->get('expires_on');
			if ( isset($expires) && strtotime($expires) < time() ) {
				$status = 'expired';
			}
		}
		return $status;
	}

	public function get($name, $default = null) {
		if ( array_key_exists($name, $this->data) ) {
			return $this->data[$name];
		} else {
			return $default;
		}
	}

	public function isExisting() {
		return ( $this->getStatus() !== 'no_license_yet' ) && ( ! $this->get('is_virtual', false) );
	}

    public function __get($key) {
		if ( array_key_exists($key, $this->data) ) {
			return $this->data[$key];
		} else {
			throw new RuntimeException('Unknown property '. $key);
		}
    }

	public function __isset($key) {
		return isset($this->data[$key]);
	}


	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}

	public function offsetGet($offset) {
		return $this->data[$offset];
	}

	public function offsetSet($offset, $value) {
		$this->data[$offset] = $value;
	}

	public function offsetUnset($offset) {
		unset($this->data[$offset]);
	}
}