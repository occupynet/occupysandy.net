<?php

if ( !function_exists('wsh_pretty_json') ) {

/**
 * Indents a flat JSON string to make it more human-readable.
 *
 * See: https://gist.github.com/906036
 * See: http://recursive-design.com/blog/2008/03/11/format-json-with-php/
 *
 * @param string $json The original JSON string to process.
 * @param string $indentStr The string used for indenting nested structures. Defaults to 4 spaces.
 * @return string Indented version of the original JSON string.
 */
function wsh_pretty_json($json, $indentStr = '    ') {

	$result      = '';
	$level       = 0;
	$strLen      = strlen($json);
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;
	$openingBracket = false;

	for ($i = 0; $i <= $strLen; $i++) {

		// Grab the next character in the string.
		$char = substr($json, $i, 1);

		// Add spaces before and after :
		if (($char == ':' && $prevChar != ' ') || ($prevChar == ':' && $char != ' ')) {
			if ($outOfQuotes) {
				$result .= ' ';
			}
		}

		// Are we inside a quoted string?
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;

			// If this character is the end of a non-empty element,
			// output a new line and indent the next line.
		} else if(($char == '}' || $char == ']') && $outOfQuotes) {
			$level --;
			if (!$openingBracket) {
				$result .= $newLine . str_repeat($indentStr, $level);
			}
		}

		// Add the character to the result string.
		$result .= $char;

		// If the last character was the beginning of a non-empty element,
		// output a new line and indent the next line.
		$openingBracket = ($char == '{' || $char == '[');
		if (($char == ',' || $openingBracket) && $outOfQuotes) {
			if ($openingBracket) {
				$level ++;
			}

			$nextChar = substr($json, $i + 1, 1);
			if ( ! ($openingBracket && ($nextChar == '}' || $nextChar == ']')) ) {
				$result .= $newLine . str_repeat($indentStr, $level);
			}
		}

		$prevChar = $char;
	}

	return $result;
}

}