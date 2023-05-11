<?php

namespace qnnp\wegar\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Tags
{
  /**
   * @param string[]|string $tags
   */
  public function __construct(public array|string $tags = [])
  {
    if (!is_array($this->tags)) {
      $this->tags = explode(',', $this->tags);
    }
  }
}
