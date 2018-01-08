<?php

namespace App\Models;

class CallRecordFile extends \App\Models\Base\CallRecordFile
{
	protected $fillable = [
		'uri',
		'last_processed_bpd_call_id',
		'last_processed_line'
	];

	public function getUri(){
		return $this->uri;
	}

	public function getLastProcessedBPDCallId(){
		return $this->last_processed_bpd_call_id;
	}

	public function getLastProcessedLine(){
		return $this->last_processed_line;
	}

	public function setUri($uri){
		$this->uri = $uri;
		return $this;
	}

	public function setLastProcessedBPDCallId($bpd_call_id){
		$this->last_processed_bpd_call_id = $bpd_call_id;
		return $this;
	}

	public function setLastProcessedLine($line){
		$this->last_processed_line = $line;
		return $this;
	}
}
