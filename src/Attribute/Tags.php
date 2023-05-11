<?php

namespace qnnp\wegar\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Tags
{
  /**
   * @param string[] $tags
   */
  public function __construct(public array $tags = [])
  {
  }
}
