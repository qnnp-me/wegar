<?php

namespace qnnp\wegar\Attribute;

use Attribute;

/**
 * 为控制器下所有相对路径路由指定基础路径
 */
#[Attribute(Attribute::TARGET_CLASS)]
class BasePath
{
  public function __construct(public string $path = '')
  {
  }
}
