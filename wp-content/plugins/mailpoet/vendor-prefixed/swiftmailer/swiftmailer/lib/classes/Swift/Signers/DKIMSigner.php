<?php
 namespace MailPoetVendor; if (!defined('ABSPATH')) exit; class Swift_Signers_DKIMSigner implements \MailPoetVendor\Swift_Signers_HeaderSigner { protected $privateKey; protected $domainName; protected $selector; private $passphrase = ''; protected $hashAlgorithm = 'rsa-sha256'; protected $bodyCanon = 'simple'; protected $headerCanon = 'simple'; protected $ignoredHeaders = ['return-path' => \true]; protected $signerIdentity; protected $bodyLen = 0; protected $maxLen = \PHP_INT_MAX; protected $showLen = \false; protected $signatureTimestamp = \true; protected $signatureExpiration = \false; protected $debugHeaders = \false; protected $signedHeaders = []; private $debugHeadersData = []; private $bodyHash = ''; protected $dkimHeader; private $bodyHashHandler; private $headerHash; private $headerCanonData = ''; private $bodyCanonEmptyCounter = 0; private $bodyCanonIgnoreStart = 2; private $bodyCanonSpace = \false; private $bodyCanonLastChar = null; private $bodyCanonLine = ''; private $bound = []; public function __construct($privateKey, $domainName, $selector, $passphrase = '') { $this->privateKey = $privateKey; $this->domainName = $domainName; $this->signerIdentity = '@' . $domainName; $this->selector = $selector; $this->passphrase = $passphrase; } public function reset() { $this->headerHash = null; $this->signedHeaders = []; $this->bodyHash = null; $this->bodyHashHandler = null; $this->bodyCanonIgnoreStart = 2; $this->bodyCanonEmptyCounter = 0; $this->bodyCanonLastChar = null; $this->bodyCanonSpace = \false; } public function write($bytes) { $this->canonicalizeBody($bytes); foreach ($this->bound as $is) { $is->write($bytes); } } public function commit() { return; } public function bind(\MailPoetVendor\Swift_InputByteStream $is) { $this->bound[] = $is; return; } public function unbind(\MailPoetVendor\Swift_InputByteStream $is) { foreach ($this->bound as $k => $stream) { if ($stream === $is) { unset($this->bound[$k]); return; } } } public function flushBuffers() { $this->reset(); } public function setHashAlgorithm($hash) { switch ($hash) { case 'rsa-sha1': $this->hashAlgorithm = 'rsa-sha1'; break; case 'rsa-sha256': $this->hashAlgorithm = 'rsa-sha256'; if (!\defined('OPENSSL_ALGO_SHA256')) { throw new \MailPoetVendor\Swift_SwiftException('Unable to set sha256 as it is not supported by OpenSSL.'); } break; default: throw new \MailPoetVendor\Swift_SwiftException('Unable to set the hash algorithm, must be one of rsa-sha1 or rsa-sha256 (%s given).', $hash); } return $this; } public function setBodyCanon($canon) { if ('relaxed' == $canon) { $this->bodyCanon = 'relaxed'; } else { $this->bodyCanon = 'simple'; } return $this; } public function setHeaderCanon($canon) { if ('relaxed' == $canon) { $this->headerCanon = 'relaxed'; } else { $this->headerCanon = 'simple'; } return $this; } public function setSignerIdentity($identity) { $this->signerIdentity = $identity; return $this; } public function setBodySignedLen($len) { if (\true === $len) { $this->showLen = \true; $this->maxLen = \PHP_INT_MAX; } elseif (\false === $len) { $this->showLen = \false; $this->maxLen = \PHP_INT_MAX; } else { $this->showLen = \true; $this->maxLen = (int) $len; } return $this; } public function setSignatureTimestamp($time) { $this->signatureTimestamp = $time; return $this; } public function setSignatureExpiration($time) { $this->signatureExpiration = $time; return $this; } public function setDebugHeaders($debug) { $this->debugHeaders = (bool) $debug; return $this; } public function startBody() { switch ($this->hashAlgorithm) { case 'rsa-sha256': $this->bodyHashHandler = \hash_init('sha256'); break; case 'rsa-sha1': $this->bodyHashHandler = \hash_init('sha1'); break; } $this->bodyCanonLine = ''; } public function endBody() { $this->endOfBody(); } public function getAlteredHeaders() { if ($this->debugHeaders) { return ['DKIM-Signature', 'X-DebugHash']; } else { return ['DKIM-Signature']; } } public function ignoreHeader($header_name) { $this->ignoredHeaders[\strtolower($header_name)] = \true; return $this; } public function setHeaders(\MailPoetVendor\Swift_Mime_SimpleHeaderSet $headers) { $this->headerCanonData = ''; $listHeaders = $headers->listAll(); foreach ($listHeaders as $hName) { if (!isset($this->ignoredHeaders[\strtolower($hName)])) { if ($headers->has($hName)) { $tmp = $headers->getAll($hName); foreach ($tmp as $header) { if ('' != $header->getFieldBody()) { $this->addHeader($header->toString()); $this->signedHeaders[] = $header->getFieldName(); } } } } } return $this; } public function addSignature(\MailPoetVendor\Swift_Mime_SimpleHeaderSet $headers) { $params = ['v' => '1', 'a' => $this->hashAlgorithm, 'bh' => \base64_encode($this->bodyHash), 'd' => $this->domainName, 'h' => \implode(': ', $this->signedHeaders), 'i' => $this->signerIdentity, 's' => $this->selector]; if ('simple' != $this->bodyCanon) { $params['c'] = $this->headerCanon . '/' . $this->bodyCanon; } elseif ('simple' != $this->headerCanon) { $params['c'] = $this->headerCanon; } if ($this->showLen) { $params['l'] = $this->bodyLen; } if (\true === $this->signatureTimestamp) { $params['t'] = \time(); if (\false !== $this->signatureExpiration) { $params['x'] = $params['t'] + $this->signatureExpiration; } } else { if (\false !== $this->signatureTimestamp) { $params['t'] = $this->signatureTimestamp; } if (\false !== $this->signatureExpiration) { $params['x'] = $this->signatureExpiration; } } if ($this->debugHeaders) { $params['z'] = \implode('|', $this->debugHeadersData); } $string = ''; foreach ($params as $k => $v) { $string .= $k . '=' . $v . '; '; } $string = \trim($string); $headers->addTextHeader('DKIM-Signature', $string); $tmp = $headers->getAll('DKIM-Signature'); $this->dkimHeader = \end($tmp); $this->addHeader(\trim($this->dkimHeader->toString()) . "\r\n b=", \true); if ($this->debugHeaders) { $headers->addTextHeader('X-DebugHash', \base64_encode($this->headerHash)); } $this->dkimHeader->setValue($string . ' b=' . \trim(\chunk_split(\base64_encode($this->getEncryptedHash()), 73, ' '))); return $this; } protected function addHeader($header, $is_sig = \false) { switch ($this->headerCanon) { case 'relaxed': $exploded = \explode(':', $header, 2); $name = \strtolower(\trim($exploded[0])); $value = \str_replace("\r\n", '', $exploded[1]); $value = \preg_replace("/[ \t][ \t]+/", ' ', $value); $header = $name . ':' . \trim($value) . ($is_sig ? '' : "\r\n"); case 'simple': } $this->addToHeaderHash($header); } protected function canonicalizeBody($string) { $len = \strlen($string); $canon = ''; $method = 'relaxed' == $this->bodyCanon; for ($i = 0; $i < $len; ++$i) { if ($this->bodyCanonIgnoreStart > 0) { --$this->bodyCanonIgnoreStart; continue; } switch ($string[$i]) { case "\r": $this->bodyCanonLastChar = "\r"; break; case "\n": if ("\r" == $this->bodyCanonLastChar) { if ($method) { $this->bodyCanonSpace = \false; } if ('' == $this->bodyCanonLine) { ++$this->bodyCanonEmptyCounter; } else { $this->bodyCanonLine = ''; $canon .= "\r\n"; } } else { } break; case ' ': case "\t": if ($method) { $this->bodyCanonSpace = \true; break; } default: if ($this->bodyCanonEmptyCounter > 0) { $canon .= \str_repeat("\r\n", $this->bodyCanonEmptyCounter); $this->bodyCanonEmptyCounter = 0; } if ($this->bodyCanonSpace) { $this->bodyCanonLine .= ' '; $canon .= ' '; $this->bodyCanonSpace = \false; } $this->bodyCanonLine .= $string[$i]; $canon .= $string[$i]; } } $this->addToBodyHash($canon); } protected function endOfBody() { if (\strlen($this->bodyCanonLine) > 0) { $this->addToBodyHash("\r\n"); } $this->bodyHash = \hash_final($this->bodyHashHandler, \true); } private function addToBodyHash($string) { $len = \strlen($string); if ($len > ($new_len = $this->maxLen - $this->bodyLen)) { $string = \substr($string, 0, $new_len); $len = $new_len; } \hash_update($this->bodyHashHandler, $string); $this->bodyLen += $len; } private function addToHeaderHash($header) { if ($this->debugHeaders) { $this->debugHeadersData[] = \trim($header); } $this->headerCanonData .= $header; } private function getEncryptedHash() { $signature = ''; switch ($this->hashAlgorithm) { case 'rsa-sha1': $algorithm = \OPENSSL_ALGO_SHA1; break; case 'rsa-sha256': $algorithm = \OPENSSL_ALGO_SHA256; break; } $pkeyId = \openssl_get_privatekey($this->privateKey, $this->passphrase); if (!$pkeyId) { throw new \MailPoetVendor\Swift_SwiftException('Unable to load DKIM Private Key [' . \openssl_error_string() . ']'); } if (\openssl_sign($this->headerCanonData, $signature, $pkeyId, $algorithm)) { return $signature; } throw new \MailPoetVendor\Swift_SwiftException('Unable to sign DKIM Hash [' . \openssl_error_string() . ']'); } } 