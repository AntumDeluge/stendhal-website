<?php

/**
 * Attributes
 */
class PharauroaAttributes {
	// TODO: Add support for RPClass
	private $content = array();

	public function writeObject(&$out) {
		$out->writeString(""); // rpClass.getName()
		$out->writeInt(size);

		foreach ($this->content As $key => $value) {	
			$out->writeShort(-1);
			$out->writeString($key);
			$out->writeString($value);
		}
	}
}