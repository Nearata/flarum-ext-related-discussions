<?php

use Flarum\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return Migration::createTable('nearata_related_discussions', function (Blueprint $table) {
    $table->unsignedInteger('discussion_id');
    $table->unsignedInteger('related_discussion_id');
    $table->timestamp('created_at')->nullable()->useCurrent();
});
