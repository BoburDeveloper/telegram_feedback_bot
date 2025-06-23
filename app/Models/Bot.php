<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphBot as BaseBot;
use DefStudio\Telegraph\Concerns\SendsAttachments; // Trait

class Bot extends BaseBot
{
    use SendsAttachments;

    // Optional: agar boshqa qo‘shimcha traitlar kerak bo‘lsa shu yerda yoziladi
}