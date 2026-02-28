const trackerState = {
    driverName: 'data_layer',
    options: {
        enabled: true,
        dataLayerName: 'dataLayer',
        measurementId: null,
        eventMap: {
            view_promotion: 'view_promotion',
            select_promotion: 'select_promotion',
            select_item: 'select_item',
            add_to_cart: 'add_to_cart',
        },
    },
};

const drivers = {
    data_layer: {
        push(eventName, payload, options) {
            if (options.enabled !== true) {
                return false;
            }

            const layerName = options.dataLayerName || 'dataLayer';
            const layer = (window[layerName] = Array.isArray(window[layerName]) ? window[layerName] : []);
            layer.push({ ecommerce: null });
            layer.push({ event: eventName, ecommerce: payload });
            return true;
        },
    },
    gtag: {
        push(eventName, payload, options) {
            if (options.enabled !== true) {
                return false;
            }

            if (typeof window.gtag !== 'function') {
                return false;
            }

            if (typeof options.measurementId === 'string' && options.measurementId.trim() !== '') {
                window.gtag('config', options.measurementId.trim());
            }

            window.gtag('event', eventName, payload);
            return true;
        },
    },
    null: {
        push() {
            return false;
        },
    },
};

const merge = (target, source) => ({ ...target, ...(source || {}) });

const resolveEventName = (eventName) => {
    const map = trackerState.options.eventMap || {};
    return map[eventName] || eventName;
};

const pushTracking = (eventName, payload) => {
    const resolvedDriver = drivers[trackerState.driverName] || drivers.data_layer;
    const resolvedEventName = resolveEventName(eventName);
    return resolvedDriver.push(resolvedEventName, payload, trackerState.options);
};

const unavailable = (message) => {
    if (!window.Swal) {
        return;
    }

    window.Swal.fire({
        title: message,
        position: 'top-end',
        icon: 'warning',
        showConfirmButton: false,
        timer: 1500,
        toast: true,
    });
};

const trackPromotionView = (campaignId, campaignName) => {
    pushTracking('view_promotion', {
        items: [
            {
                promotion_id: campaignId,
                promotion_name: campaignName,
                creative_name: 'campaign-page',
                creative_slot: 'campaign-page',
                location_id: 'campaign-page',
            },
        ],
    });
};

const trackPromotionSelect = (link, campaignId, campaignName) => {
    const dataset = link.dataset;
    pushTracking('select_promotion', {
        items: [
            {
                promotion_id: dataset.promotionId || campaignId,
                promotion_name: dataset.promotionName || campaignName,
                creative_name: dataset.creativeName || 'campaign-page',
                creative_slot: dataset.creativeSlot || 'campaign-page',
                location_id: dataset.locationId || 'campaign-page',
            },
        ],
    });
};

const trackSelectItem = (link, affiliation = '', currency = 'TWD') => {
    const dataset = link.dataset;
    pushTracking('select_item', {
        item_list_id: dataset.itemListId || '',
        item_list_name: dataset.itemListName || '',
        items: [
            {
                item_id: dataset.itemId || '',
                item_name: dataset.itemName || '',
                affiliation,
                coupon: '',
                currency,
                discount: 0,
                index: dataset.itemIndex || 0,
                item_brand: '',
                item_category: dataset.itemCategory || '',
                item_list_id: dataset.itemListId || '',
                item_list_name: dataset.itemListName || '',
                item_variant: '',
                location_id: '',
                price: dataset.itemPrice || 0,
                quantity: 1,
            },
        ],
    });
};

const trackAddToCart = (button, payload, config) => {
    pushTracking('add_to_cart', {
        currency: config.currency,
        value: button.dataset.price,
        items: [
            {
                item_id: payload.prod_no,
                item_name: payload.name,
                affiliation: config.affiliation,
                coupon: '',
                currency: config.currency,
                discount: '',
                index: 0,
                item_brand: '',
                item_category: button.dataset.category || '',
                item_list_id: '',
                item_list_name: '',
                item_variant: '',
                location_id: '',
                price: button.dataset.price,
                quantity: 1,
            },
        ],
    });
};

let campaignType1Bound = false;

const initType1 = (config = {}) => {
    if (campaignType1Bound) {
        return;
    }

    const options = {
        campaignId: String(config.campaignId ?? ''),
        campaignName: String(config.campaignName ?? ''),
        currency: String(config.currency ?? 'TWD'),
        affiliation: String(config.affiliation ?? ''),
    };

    if (options.campaignId === '' && options.campaignName === '') {
        return;
    }

    campaignType1Bound = true;
    trackPromotionView(options.campaignId, options.campaignName);

    document.addEventListener(
        'click',
        (event) => {
            const promotionLink = event.target.closest('a.js-select-promotion');
            if (promotionLink) {
                trackPromotionSelect(promotionLink, options.campaignId, options.campaignName);
            }

            const selectLink = event.target.closest('a.js-select-item');
            if (selectLink) {
                trackSelectItem(selectLink, options.affiliation, options.currency);
            }

            const button = event.target.closest('.js-add-to-cart');
            if (!button) {
                return;
            }

            const status = button.dataset.status;
            if (status !== 'available') {
                unavailable(status === 'coming_soon' ? '商品尚未開賣' : '商品已售完');
                return;
            }

            const payload = {
                id: button.dataset.id,
                prod_no: button.dataset.prodNo,
                name: button.dataset.title,
                quantity: 1,
            };

            if (typeof window.cptwAddToCart !== 'function') {
                return;
            }

            window.cptwAddToCart(payload)
                .then((data) => {
                    if (!data) {
                        return;
                    }

                    trackAddToCart(button, payload, options);
                    const count = data.count || 0;
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count } }));
                })
                .catch(() => {
                    unavailable('系統忙線中，請稍後再試');
                });
        },
        { passive: true },
    );
};

const setTracker = (driver, options = {}) => {
    trackerState.driverName = Object.prototype.hasOwnProperty.call(drivers, driver) ? driver : 'data_layer';
    trackerState.options = merge(trackerState.options, options);
};

const bootstrap = () => {
    const runtime = window.__CAMPAIGN_KIT__ || {};
    const tracking = runtime.tracking || {};

    setTracker(String(tracking.driver || 'data_layer'), {
        enabled: tracking.enabled === true,
        dataLayerName: tracking.dataLayerName || 'dataLayer',
        measurementId: tracking.measurementId || null,
        eventMap: tracking.eventMap || trackerState.options.eventMap,
    });
};

window.CampaignKit = window.CampaignKit || {
    initType1,
    setTracker,
};

window.cptwInitCampaignType1 = window.cptwInitCampaignType1 || initType1;

bootstrap();

export { initType1, setTracker };
