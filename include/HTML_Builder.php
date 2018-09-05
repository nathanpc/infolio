<?php
/**
 * HTML_Builder.php
 * A simple helper class to build HTML faster and easier.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

class Builder {
	/**
	 * Builds a root node.
	 *
	 * @param  string      $tag        Tag name.
	 * @param  array       $attributes Node attributes.
	 * @param  mixed       $child      Child element.
	 * @return DOMDocument             Completed node.
	 */
	public static function root($tag, $attributes = NULL, $child = NULL) {
		// Create document.
		$xml = new DOMDocument("1.0", "utf-8");

		// Populate the node if you passed a string for a child.
		$root = $xml->importNode(self::child($tag, $attributes, $child), true);

		// Append the root node and return it.
		$xml->appendChild($root);
		return $xml;
	}
	
	/**
	 * Builds a child node.
	 *
	 * @param  string     $tag        Tag name.
	 * @param  array      $attributes Node attributes.
	 * @param  mixed      $child      Child element.
	 * @return DOMElement             Completed node.
	 */
	public static function child($tag, $attributes = NULL, $child = NULL) {
		// Create document.
		$xml = new DOMDocument("1.0", "utf-8");

		// Populate the node if you passed a string for a child.
		$root = $xml->createElement($tag);
		if (is_string($child)) {
			$root = $xml->createElement($tag, $child);
		}

		// Set the root node attributes.
		if (!is_null($attributes)) {
			foreach ($attributes as $key => $value) {
				$attr = $xml->createAttribute($key);
				$attr->value = $value;

				$root->appendChild($attr);
			}
		}

		// Append a child if there is one.
		if (!is_null($child) && !is_string($child)) {
			if (is_array($child)) {
				foreach ($child as $cn) {
					$root->appendChild($xml->importNode($cn, true));
				}
			} else {
				$root->appendChild($xml->importNode($child, true));
			}
		}

		// Append the root node and return it.
		//$xml->appendChild($root);
		return $root;
	}
}

?>
