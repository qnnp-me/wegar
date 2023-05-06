<?php

namespace qnnp\wegar\Attribute;

use Attribute;

/**
 * 为控制器下所有路由增加前缀
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Prefix
{
  public function __construct(public string $path = '')
  {
  }
}
