<?php
function emptyFields(array $fields): array
{
    $emptyFields = [];
    foreach ($fields as $name => $value)
    {
        if (empty($value))
        {
            $emptyFields[] = $name;
        }
    }
    return $emptyFields;
}