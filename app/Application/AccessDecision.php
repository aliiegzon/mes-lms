<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Application;

use MyEdSpace\Lms\Enums\AccessDecisionReasonEnum;

final class AccessDecision
{
    /**
     * @param bool $allowed
     * @param AccessDecisionReasonEnum|null $reason
     */
    private function __construct(
        public readonly bool $allowed,
        public readonly ?AccessDecisionReasonEnum $reason = null
    ) {}

    /**
     * @return self
     */
    public static function allow(): self
    {
        return new self(true, null);
    }

    /**
     * @param AccessDecisionReasonEnum $reason
     * @return self
     */
    public static function deny(AccessDecisionReasonEnum $reason): self
    {
        return new self(false, $reason);
    }
}
