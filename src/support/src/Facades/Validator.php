<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Validator as HyperfValidator;

/**
 * @method static array validate(array $data, array $rules, array $messages = [], array $customAttributes = [])
 * @method static bool fails()
 * @method static bool passes()
 * @method static array errors()
 * @method static array validated()
 * @method static Validator sometimes(string $attribute, string|array $rules, callable $callback)
 * @method static Validator after(callable $callback)
 * @method static Validator addRules(array $rules)
 * @method static mixed getValue(string $attribute)
 * @method static array getMessageBag()
 * @method static array getCustomMessages()
 * @method static array getCustomAttributes()
 * @method static array getRules()
 * @method static Validator setRules(array $rules)
 * @method static Validator setCustomMessages(array $messages)
 * @method static Validator setCustomAttributes(array $attributes)
 * @method static Validator setAttributeNames(array $attributes)
 * @method static Validator addFailure(string $attribute, string $rule, array $parameters = [])
 * @method static Validator addReplacers(string $rule, \Closure|string $replacer)
 * @method static Validator setPresenceVerifier(PresenceVerifierInterface $presenceVerifier)
 * @method static mixed getPresenceVerifier()
 *
 * @see HyperfValidator
 */
class Validator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ValidatorFactoryInterface::class;
    }
}
