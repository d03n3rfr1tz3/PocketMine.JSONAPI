<?php
namespace JSONAPI\api;

interface IHandler
{
	public function handles();
	public function handle($name, $arguments = null);
}