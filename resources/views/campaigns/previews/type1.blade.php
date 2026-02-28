<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $campaignTitle }}</title>
    <link href="/css/campaigns/type1.css" rel="stylesheet" type="text/css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #f2f4f7;
        }

        .campaign-preview-shell {
            margin: 0 auto;
            max-width: 1240px;
            padding: 16px;
        }

        .campaign-preview-shell a {
            pointer-events: none;
        }

        .campaign-preview-demo-banner {
            align-items: center;
            background: linear-gradient(120deg, #27598a 0%, #4ca1b6 45%, #86c8bb 100%);
            border-radius: 6px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            min-height: 136px;
            padding: 16px 20px;
        }

        .campaign-preview-demo-banner__title {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin: 0;
        }

        .campaign-preview-demo-banner__subtitle {
            font-size: 0.95rem;
            margin: 8px 0 0;
            opacity: 0.95;
        }

        .campaign-preview-demo-banner__tag {
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 9999px;
            font-size: 0.75rem;
            padding: 6px 12px;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="campaign-preview-root campaign-preview-shell">
    <div class="hide_menu campaign-type1">
        <div class="campaign-type1__inner">
            <div class="campaign-type1__banner shadow-sm">
                <div class="campaign-preview-demo-banner" role="img" aria-label="{{ $campaignTitle }}">
                    <div>
                        <p class="campaign-preview-demo-banner__title">{{ $campaignTitle }}</p>
                        <p class="campaign-preview-demo-banner__subtitle">活動版型示範 Banner（預覽專用）</p>
                    </div>
                    <span class="campaign-preview-demo-banner__tag">DESKTOP PREVIEW</span>
                </div>
            </div>

            <section class="campaign-type1__section shadow-sm">
                <header class="campaign-type1__section-head">
                    <h3 class="campaign-type1__title">{{ $primaryTitle }}</h3>
                </header>

                <div class="campaign-type1__intro">
                    <p>{{ $primaryIntro }}</p>
                </div>

                <ul class="campaign-type1__primary-list">
                    @foreach ($primaryItems as $item)
                        <li class="campaign-type1__card campaign-type1__card--primary">
                            <div class="campaign-type1__image-link">
                                <img
                                    class="campaign-thumb"
                                    src="{{ $item['image_url'] }}"
                                    alt="{{ $item['title'] }}"
                                    title="{{ $item['title'] }}"
                                    width="167"
                                    height="230"
                                >
                            </div>
                            <div class="campaign-type1__content">
                                <h4 class="campaign-title">{{ $item['title'] }}</h4>
                                <ul class="list_details campaign-type1__summary">
                                    <li class="campaign-author">{{ $item['author'] }}</li>
                                    <li>售價 {{ number_format((float) $item['price']) }}</li>
                                </ul>
                                <p class="campaign-type1__description line_clamp row_3">{{ $item['summary'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="campaign-type1__section shadow-sm">
                <header class="campaign-type1__section-head">
                    <h3 class="campaign-type1__title">{{ $secondaryTitle }}</h3>
                </header>

                <div class="campaign-type1__intro">
                    <p>{{ $secondaryIntro }}</p>
                </div>

                <div class="campaign-type1__secondary-grid">
                    @foreach ($secondaryItems as $item)
                        <article class="campaign-type1__card campaign-type1__card--secondary">
                            <div class="campaign-type1__image-link">
                                <img
                                    class="campaign-thumb"
                                    src="{{ $item['image_url'] }}"
                                    alt="{{ $item['title'] }}"
                                    title="{{ $item['title'] }}"
                                    width="167"
                                    height="230"
                                >
                            </div>
                            <div class="campaign-type1__content">
                                <h4 class="campaign-title">{{ $item['title'] }}</h4>
                                <ul class="list_details campaign-type1__summary">
                                    <li class="campaign-author">{{ $item['author'] }}</li>
                                    <li>售價 {{ number_format((float) $item['price']) }}</li>
                                </ul>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="campaign-type1__section shadow-sm">
                <header class="campaign-type1__section-head">
                    <h3 class="campaign-type1__title">{{ $additionalTitle }}</h3>
                </header>

                <div class="campaign-type1__intro">
                    <p>{{ $additionalIntro }}</p>
                </div>

                <div class="campaign-type1__additional-grid">
                    @foreach ($additionalItems as $item)
                        <article class="campaign-type1__card campaign-type1__card--additional">
                            <div class="campaign-type1__image-link">
                                <img
                                    class="campaign-thumb campaign-thumb--small"
                                    src="{{ $item['image_url'] }}"
                                    alt="{{ $item['title'] }}"
                                    title="{{ $item['title'] }}"
                                    width="167"
                                    height="230"
                                >
                            </div>
                            <div class="campaign-type1__content">
                                <h4 class="campaign-title">{{ $item['title'] }}</h4>
                                <ul class="list_details campaign-type1__summary">
                                    <li class="campaign-author">{{ $item['author'] }}</li>
                                    <li>售價 {{ number_format((float) $item['price']) }}</li>
                                </ul>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
</body>
</html>
