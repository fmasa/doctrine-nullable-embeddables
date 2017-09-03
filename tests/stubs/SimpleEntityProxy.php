<?php


class SimpleEntityProxy extends SimpleEntity implements \Doctrine\Common\Persistence\Proxy
{
	public function __load()
	{

	}

	public function __isInitialized(): bool
	{
		return false;
	}


}
