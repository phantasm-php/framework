<?php

namespace Framework\Core;

enum Scope
{
    case HTTP;
    case CONSOLE;
    case WORKER;
}
