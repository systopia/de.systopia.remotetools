# Upgrade notes for developers

# 2.0.0

(Not released, yet.)

* The interface `\Civi\RemoteTools\Form\FormSpec\FieldDataTransformerInterface`
will require `?array $defaultValuesInList = NULL` as third parameter. It should
be added already now in implementations.
