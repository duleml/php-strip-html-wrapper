<?php
namespace App;

interface StripState {
    const __default = 1;

    const ContentState = 1;
    const TagState = 2;
    const AttributeState = 3;
}