<?php

class Action {
	private $url;
	private $icon;
	private $label;
	private $method;
	private $ajax;
	private $confirm;
	
	public function __construct($url, $icon = null, $label = null) {
		$this->url = $url;
		$this->icon = $icon;
		$this->label = $label;
		$this->method = 'GET';
		$this->ajax = false;
		$this->confirm = null;
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}
	
	public function getIcon() {
		return $this->icon;
	}
	
	public function setIcon($icon) {
		$this->icon = $icon;
		return $this;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}
	
	public function getMethod() {
		return $this->method;
	}
	
	public function setMethod($method) {
		$this->method = $method;
		return $this;
	}
	
	public function isAjax() {
		return $this->ajax;
	}
	
	public function setAjax($ajax) {
		$this->ajax = $ajax;
		return $this;
	}
	
	public function getConfirm() {
		return $this->confirm;
	}
	
	public function setConfirm($confirm) {
		$this->confirm = $confirm;
		return $this;
	}
}

?>