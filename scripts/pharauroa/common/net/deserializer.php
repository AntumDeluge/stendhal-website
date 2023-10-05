<?php

class PharauroaDeserializer {
	// Fields
	private $data;
	private $protocolVersion = PHARAUROA_NETWORK_PROTOCOL_VERSION;

	// Constructors
	public function __construct($data) {
		$this->data = $data;
	}

	/**
	 * sets the protocol version
	 *
	 * @param $protocolVersion int version of protocol
	 */
	public function setProtocolVersion($protocolVersion) {
		$this->$protocolVersion = min($protocolVersion, PHARAUROA_NETWORK_PROTOCOL_VERSION);
	}

	/**
	 * gets the protocol version
	 *
	 * @return int version of protocol
	 */
	public function getProtocolVersion() {
		return $this->protocolVersion;
	}

	/**
	 * This method reads a byte
	 *
	 * @return int the byte
	 */
	public function readByte() {
		if (strlen($this->data) < 1) {
			throw new PharauroaIOException('Trying to read byte beyond end of stream.');
		}
		$output = ord($this->data[0]);
		$this->data = substr($this->data, 1);
		return $output;
	}

	/**
	 * This method reads a byte
	 *
	 * @return int the shot
	 */
	public function readShort() {
		if (strlen($this->data) < 2) {
			throw new PharauroaIOException('Trying to read short beyond end of stream.');
		}
		$output = unpack("s", $this->data);
		$this->data = substr($this->data, 2);
		return $output[1];
	}

	/**
	 * This method reads an integer
	 *
	 * @return int the integer
	 */
	public function readInt() {
		if (strlen($this->data) < 4) {
			throw new PharauroaIOException('Trying to read integer beyond end of stream.');
		}
		$output = unpack("I", $this->data);
		$this->data = substr($this->data, 4);
		return $output[1];
	}

	/**
	 * This method reads a long string
	 *
	 * @return string the string
	 */
		public function readString() {
		$length = $this->readInt();
		if (strlen($this->data) < $length) {
			throw new PharauroaIOException('Trying to read string of length '.$length.' beyond end of stream.');
		}
		$output = substr($this->data, 0, $length);
		$this->data = substr($this->data, $length);
		return $output;
	}

	/**
	 * This method reads a short string
	 *
	 * @return string the string
	 */
	public function read255LongString() {
		$length = $this->readByte();
		if (strlen($this->data) < $length) {
			throw new PharauroaIOException('Trying to read short string of length '.$length.' beyond end of stream.');
		}
		$output = substr($this->data, 0, $length);
		$this->data = substr($this->data, $length);
		return $output;
	}
}
