<?php

namespace Makhnanov\Php81SelfFilling\Behaviour;

enum ErrorBehaviour
{
    case THROW_AFTER_FIRST;
    case IGNORE;
    case REPLACE_WITH_DEFAULT;
}
