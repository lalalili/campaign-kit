<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Tests\Support\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $url_slug
 * @property string $campaign_title
 * @property int $type
 */
class FakeCampaign extends Model
{
    protected $table = 'fake_campaigns';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $guarded = [];
}
