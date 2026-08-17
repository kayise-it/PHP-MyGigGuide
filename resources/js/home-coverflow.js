export function homeHeroGallery(catalog = {}, initialCategory = '', initialDays = 7) {
    return {
        catalog,
        heroDays: initialDays,
        categorySlug: initialCategory || '',
        active: 0,
        init() {
            this.$nextTick(() => this.syncActiveFromScroll());
        },
        get events() {
            return this.catalog[this.categorySlug]?.[this.heroDays] ?? [];
        },
        get focused() {
            return this.events[this.active] ?? null;
        },
        preserveScroll(update) {
            const scrollY = window.scrollY;
            update();
            this.active = 0;
            this.$nextTick(() => {
                this.resetTrack();
                window.scrollTo(0, scrollY);
            });
        },
        setHeroDays(days) {
            if (this.heroDays === days) {
                return;
            }
            this.preserveScroll(() => {
                this.heroDays = days;
            });
        },
        setCategory(slug) {
            const next = slug || '';
            if (this.categorySlug === next) {
                return;
            }
            this.preserveScroll(() => {
                this.categorySlug = next;
            });
        },
        resetTrack() {
            const el = this.$refs.track;
            if (el) {
                el.scrollLeft = 0;
            }
            this.syncActiveFromScroll();
        },
        scrollTo(index) {
            const el = this.$refs.track;
            if (!el) {
                return;
            }
            const cards = el.querySelectorAll('[data-coverflow-index]');
            const target = cards[index];
            if (!target) {
                return;
            }

            const left = target.offsetLeft - (el.clientWidth - target.offsetWidth) / 2;
            el.scrollTo({ left, behavior: 'smooth' });
            this.active = index;
        },
        prev() {
            if (this.active <= 0) {
                return;
            }
            this.scrollTo(this.active - 1);
        },
        next() {
            if (this.active >= this.events.length - 1) {
                return;
            }
            this.scrollTo(this.active + 1);
        },
        onScroll() {
            window.clearTimeout(this._scrollSyncTimer);
            this.syncActiveFromScroll();
            this._scrollSyncTimer = window.setTimeout(() => this.syncActiveFromScroll(), 120);
        },
        syncActiveFromScroll() {
            const el = this.$refs.track;
            if (!el) {
                return;
            }
            const cards = [...el.querySelectorAll('[data-coverflow-index]')];
            if (cards.length === 0) {
                this.active = 0;
                return;
            }

            const center = el.scrollLeft + el.clientWidth / 2;
            let best = 0;
            let bestDist = Infinity;
            cards.forEach((card, i) => {
                const cardCenter = card.offsetLeft + card.offsetWidth / 2;
                const dist = Math.abs(center - cardCenter);
                if (dist < bestDist) {
                    bestDist = dist;
                    best = i;
                }
            });

            if (this.active !== best) {
                this.active = best;
            }
        },
        cardClass(index) {
            const d = Math.abs(this.active - index);
            if (d === 0) {
                return 'z-30 opacity-100 scale-100 brightness-100';
            }
            if (d === 1) {
                return 'z-10 opacity-35 scale-[0.58] brightness-75 saturate-50';
            }
            return 'z-0 opacity-20 scale-[0.45] brightness-50 saturate-0';
        },
        frameClass(index) {
            const d = Math.abs(this.active - index);
            if (d === 0) {
                return 'ring-2 shadow-2xl';
            }
            if (d === 1) {
                return 'ring-1 ring-white/10 shadow-md';
            }
            return 'ring-0 shadow-none';
        },
        cardWidth() {
            return 'min(78vw, 280px)';
        },
    };
}

/** @deprecated Use homeHeroGallery */
export function homeCoverflowGallery(eventMeta = []) {
    return homeHeroGallery({ '': { 7: eventMeta } }, '', 7);
}
