<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec;

use Civi\RemoteTools\JsonSchema\JsonSchema;

/**
 * @phpstan-import-type conditionListT from \Civi\RemoteTools\Form\FormSpec\FormSpec
 * @phpstan-import-type fieldNameValuePairsT from \Civi\RemoteTools\Form\FormSpec\FormSpec
 * @phpstan-import-type limitValidationT from \Civi\RemoteTools\Form\FormSpec\FormSpec
 */
final class LimitValidationSchemaFactory {

  /**
   * @phpstan-param limitValidationT $limitValidation
   */
  public static function createSchema($limitValidation): ?JsonSchema {
    $condition = self::createCondition($limitValidation);

    if (NULL === $condition) {
      return NULL;
    }

    if (FALSE === $condition) {
      return JsonSchema::fromArray([
        'condition' => FALSE,
      ]);
    }

    return JsonSchema::fromArray([
      'condition' => $condition,
      'rules' => [
        [
          'keyword' => ['const' => 'required'],
          'validate' => TRUE,
        ],
      ],
    ]);
  }

  /**
   * @phpstan-param conditionListT $conditionList
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  private static function createConditionFromConditionList(array $conditionList): JsonSchema {
    // phpcs:enable
    $properties = [];
    foreach ($conditionList as [$fieldName, $operator, $value]) {
      switch ($operator) {
        case '=':
          $properties[$fieldName]['const'] = $value;
          break;

        case '!=':
          $properties[$fieldName]['not']['const'] = $value;
          break;

        case '>':
          if (!is_int($value) && !is_float($value)) {
            throw new \InvalidArgumentException('Expected integer or float for operator ">", got ' . gettype($value));
          }

          $properties[$fieldName]['exclusiveMinimum'] = $value;
          break;

        case '>=':
          if (!is_int($value) && !is_float($value)) {
            throw new \InvalidArgumentException('Expected integer or float for operator ">=", got ' . gettype($value));
          }

          $properties[$fieldName]['minimum'] = $value;
          break;

        case '<':
          if (!is_int($value) && !is_float($value)) {
            throw new \InvalidArgumentException('Expected integer or float for operator "<", got ' . gettype($value));
          }

          $properties[$fieldName]['exclusiveMaximum'] = $value;
          break;

        case '<=':
          if (!is_int($value) && !is_float($value)) {
            throw new \InvalidArgumentException('Expected integer or float for operator "<=", got ' . gettype($value));
          }

          $properties[$fieldName]['maximum'] = $value;
          break;

        case '=~':
          if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string for operator "=~", got ' . gettype($value));
          }

          $properties[$fieldName]['pattern'] = $value;
          break;

        case 'IN':
          if (!is_array($value)) {
            throw new \InvalidArgumentException('Expected array for operator "IN", got ' . gettype($value));
          }

          $properties[$fieldName]['enum'] = $value;
          break;

        case 'NOT IN':
          if (!is_array($value)) {
            throw new \InvalidArgumentException('Expected array for operator "NOT IN", got ' . gettype($value));
          }

          $properties[$fieldName]['not']['enum'] = $value;
          break;

        default:
          throw new \InvalidArgumentException(sprintf('Unknown operator "%s"', $operator));
      }
    }

    return JsonSchema::fromArray(['properties' => $properties]);
  }

  /**
   * @phpstan-param fieldNameValuePairsT $fieldNameValuePairs
   */
  private static function createConditionFromPairs(array $fieldNameValuePairs): JsonSchema {
    $properties = array_map(
      fn ($value) => is_array($value) ? ['enum' => $value] : ['const' => $value],
      $fieldNameValuePairs
    );

    return JsonSchema::fromArray(['properties' => $properties]);
  }

  /**
   * @phpstan-param limitValidationT $limitValidation
   *
   * @return bool|null|JsonSchema
   */
  private static function createCondition($limitValidation) {
    if (NULL === $limitValidation) {
      return NULL;
    }

    if (is_bool($limitValidation)) {
      return $limitValidation;
    }

    if (is_string($limitValidation)) {
      return self::createValidateCondition($limitValidation);
    }

    if (is_array($limitValidation)) {
      if (is_string(key($limitValidation))) {
        /** @phpstan-var fieldNameValuePairsT $limitValidation */
        return self::createConditionFromPairs($limitValidation);
      }

      /** @phpstan-var conditionListT $limitValidation */
      return self::createConditionFromConditionList($limitValidation);
    }

    throw new \InvalidArgumentException('Unsupported limit validation specification');
  }

  private static function createValidateCondition(string $expression): JsonSchema {
    $variables = [];

    $expression = preg_replace_callback(
      '/@{([^\s}]+)}/',
      function (array $match) use (&$variables) {
        $variables[$match[1]] = ['$data' => '/' . $match[1]];

        return $match[1];
      },
      $expression
    );

    return JsonSchema::fromArray([
      'evaluate' => [
        'expression' => $expression,
        'variables' => $variables,
      ],
    ]);
  }

}
