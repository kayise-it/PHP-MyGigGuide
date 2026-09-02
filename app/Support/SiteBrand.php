<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Public-site white-label brand resolved from the request hostname (Phase A: skin only).
 */
final class SiteBrand
{
    private function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly string $tagline,
        public readonly string $logoPath,
        public readonly string $faviconPath,
        public readonly bool $isRogues,
        public readonly bool $isFm919,
    ) {}

    public static function current(?Request $request = null): self
    {
        $request ??= request();
        $host = strtolower($request->getHost());

        if ($host === '919fm.mygigguide.co.za' || str_starts_with($host, '919fm.')) {
            return self::fm919();
        }

        return self::mygigguide();
    }

    public static function mygigguide(): self
    {
        return new self(
            key: 'mygigguide',
            name: 'My Gig Guide',
            tagline: 'Discover amazing events, artists, and venues in your area.',
            logoPath: 'logos/mgg-headphones-logo.png',
            faviconPath: 'logos/favicon.png',
            isRogues: false,
            isFm919: false,
        );
    }

    public static function rogues(): self
    {
        return new self(
            key: 'rogues',
            name: 'Rogues on Radio',
            tagline: 'Live radio and local gigs — discover what\'s on near you.',
            logoPath: 'logos/RouguesonRadioLogo.png',
            faviconPath: 'logos/RouguesonRadioLogo.png',
            isRogues: true,
            isFm919: false,
        );
    }

    public static function fm919(): self
    {
        return new self(
            key: 'fm919',
            name: '919 FM',
            tagline: 'Live radio and local gigs — discover what\'s on near you.',
            logoPath: 'logos/fm919_brand_logo.png',
            faviconPath: 'logos/fm919_brand_logo.png',
            isRogues: false,
            isFm919: true,
        );
    }

    public function isDarkTenant(): bool
    {
        return $this->isRogues || $this->isFm919;
    }

    public function isPartnerTenant(): bool
    {
        return $this->isRogues || $this->isFm919;
    }

    /**
     * Public listener tools shown in nav / footer on station hostnames.
     *
     * @return list<array{label: string, route: string}>
     */
    public function partnerEngagementNavLinks(): array
    {
        if ($this->isFm919) {
            return [
                ['label' => 'Listen live', 'route' => 'rogues.listen'],
                ['label' => 'Poll', 'route' => 'fm919.poll'],
                ['label' => 'Request', 'route' => 'fm919.request'],
            ];
        }

        if ($this->isRogues) {
            return [
                ['label' => 'Listen live', 'route' => 'rogues.listen'],
            ];
        }

        return [];
    }

    private function accentPick(string $fm919, string $rogues, string $default): string
    {
        if ($this->isFm919) {
            return $fm919;
        }

        if ($this->isRogues) {
            return $rogues;
        }

        return $default;
    }

    public function logoUrl(): string
    {
        return asset($this->logoPath);
    }

    public function faviconUrl(): string
    {
        return asset($this->faviconPath);
    }

    /** Square brand image for Open Graph / Twitter cards. */
    public function socialShareImagePath(): string
    {
        if ($this->isFm919) {
            return 'logos/fm919_brand_logo.png';
        }

        return $this->isDarkTenant()
            ? 'logos/RouguesonRadioLogo.png'
            : 'logos/mgg-play-store-icon-512.png';
    }

    public function socialShareImageUrl(): string
    {
        return asset($this->socialShareImagePath());
    }

    public function bodyClass(): string
    {
        if ($this->isFm919) {
            return 'brand-fm919';
        }

        return $this->isDarkTenant() ? 'brand-rogues' : 'brand-mygigguide';
    }

    /** Outer `<body>` background and default text colour. */
    public function pageShellClass(): string
    {
        return $this->isDarkTenant()
            ? 'bg-slate-950 text-slate-100'
            : 'bg-black text-slate-100';
    }

    /** Full-page content wrapper (home, listings, map). */
    public function pageContentShellClass(): string
    {
        return $this->isDarkTenant()
            ? 'min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950'
            : 'min-h-screen bg-gradient-to-br from-black via-[#0a0a12] to-black';
    }

    public function sectionSurfaceClass(): string
    {
        return $this->isDarkTenant()
            ? 'py-8 bg-slate-900/40'
            : 'py-8 bg-[#12121A]/60';
    }

    public function sectionAltClass(): string
    {
        return $this->isDarkTenant()
            ? 'py-8 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900'
            : 'py-8 bg-gradient-to-br from-[#12121A] via-black to-[#12121A]';
    }

    public function ctaSectionClass(): string
    {
        return $this->isDarkTenant()
            ? 'py-8 bg-gradient-to-r from-slate-900 to-slate-950 border-t border-slate-800'
            : 'py-8 bg-gradient-to-r from-[#12121A] to-black border-t border-white/5';
    }

    public function heroTitleClass(): string
    {
        return 'text-4xl md:text-6xl font-bold text-white mb-6';
    }

    public function headingClass(): string
    {
        return 'text-3xl md:text-4xl font-bold text-white mb-4';
    }

    public function pageTitleClass(): string
    {
        return 'text-3xl font-bold text-white';
    }

    public function pageSubtitleClass(): string
    {
        return 'text-slate-400 mt-2';
    }

    public function subtitleClass(): string
    {
        return 'text-xl md:text-2xl text-slate-300 mb-6 max-w-3xl mx-auto';
    }

    public function bodyTextClass(): string
    {
        return 'text-lg text-slate-400 max-w-2xl mx-auto';
    }

    public function accentGradientClass(): string
    {
        if ($this->isFm919) {
            return 'bg-gradient-to-r from-yellow-200 to-yellow-400 bg-clip-text text-transparent';
        }

        return $this->isRogues
            ? 'bg-gradient-to-r from-sky-300 to-cyan-400 bg-clip-text text-transparent'
            : 'bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent';
    }

    public function filterPanelClass(): string
    {
        return $this->isDarkTenant()
            ? 'bg-slate-900 rounded-2xl shadow-sm border border-slate-700 p-6 mb-8'
            : 'bg-[#12121A] rounded-2xl shadow-sm border border-white/10 p-6 mb-8';
    }

    public function listingHeaderBarClass(): string
    {
        return $this->isDarkTenant()
            ? 'bg-slate-900/80 border-b border-slate-800'
            : 'bg-[#12121A]/90 border-b border-white/10';
    }

    public function listingCardShellClass(): string
    {
        return $this->isDarkTenant()
            ? 'bg-slate-900 rounded-lg shadow-sm border border-slate-700 p-6'
            : 'bg-[#12121A] rounded-lg shadow-sm border border-white/10 p-6';
    }

    public function directoryCardClass(): string
    {
        return $this->isDarkTenant()
            ? 'group relative rounded-xl shadow-sm border border-slate-700 overflow-hidden cursor-pointer hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 block bg-slate-900'
            : 'group relative rounded-xl shadow-sm border border-white/10 overflow-hidden cursor-pointer hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 block bg-[#12121A]';
    }

    public function navBarClass(): string
    {
        return $this->isDarkTenant()
            ? 'site-nav bg-slate-900 border-b border-slate-800 shadow-lg'
            : 'site-nav bg-[#12121A] border-b border-white/10 shadow-lg';
    }

    public function navTitleClass(): string
    {
        return 'text-xl font-bold text-white whitespace-nowrap';
    }

    public function navLogoWrapClass(): string
    {
        if ($this->isFm919) {
            return 'bg-slate-800 ring-1 ring-yellow-500/30';
        }

        return $this->isRogues
            ? 'bg-slate-800 ring-1 ring-sky-500/30'
            : 'bg-black ring-1 ring-indigo-500/30';
    }

    public function navIconButtonClass(): string
    {
        return 'text-slate-300 hover:text-white hover:bg-slate-800';
    }

    public function navUserButtonClass(): string
    {
        return $this->navIconButtonClass();
    }

    public function navDividerClass(): string
    {
        return 'border-slate-700';
    }

    public function navLinkClass(bool $active): string
    {
        if (! $active) {
            return 'flex items-center text-slate-300 px-3 py-2.5 rounded-xl text-sm font-medium border border-transparent hover:bg-slate-800 hover:text-white hover:shadow-sm transition-all duration-200';
        }

        return $this->accentPick(
            'flex items-center text-yellow-400 bg-slate-800 px-3 py-2.5 rounded-xl text-sm font-medium border border-yellow-500/40 shadow-sm',
            'flex items-center text-sky-400 bg-slate-800 px-3 py-2.5 rounded-xl text-sm font-medium border border-sky-500/40 shadow-sm',
            'flex items-center text-indigo-400 bg-slate-800 px-3 py-2.5 rounded-xl text-sm font-medium border border-indigo-500/40 shadow-sm',
        );
    }

    public function mobileNavPanelClass(): string
    {
        return $this->isDarkTenant()
            ? 'md:hidden bg-slate-900 border-t border-slate-800'
            : 'md:hidden bg-[#12121A] border-t border-white/10';
    }

    public function mobileNavLinkClass(bool $active): string
    {
        if (! $active) {
            return 'block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-md transition-colors duration-200';
        }

        return $this->accentPick(
            'block px-3 py-2 text-base font-medium text-yellow-400 bg-slate-800 rounded-md',
            'block px-3 py-2 text-base font-medium text-sky-400 bg-slate-800 rounded-md',
            'block px-3 py-2 text-base font-medium text-indigo-400 bg-slate-800 rounded-md',
        );
    }

    public function focusRingClass(): string
    {
        return $this->accentPick(
            'focus:ring-yellow-400 focus:ring-offset-slate-900',
            'focus:ring-sky-400 focus:ring-offset-slate-900',
            'focus:ring-indigo-500 focus:ring-offset-black',
        );
    }

    public function dropdownPanelClass(): string
    {
        return $this->isDarkTenant()
            ? 'absolute right-0 mt-2 w-80 max-h-96 overflow-hidden bg-slate-900 rounded-lg shadow-xl border border-slate-700 z-50'
            : 'absolute right-0 mt-2 w-80 max-h-96 overflow-hidden bg-[#12121A] rounded-lg shadow-xl border border-white/10 z-50';
    }

    public function dropdownMenuClass(): string
    {
        return $this->isDarkTenant()
            ? 'absolute right-0 mt-2 w-56 bg-slate-900 rounded-lg shadow-xl py-2 z-50 border border-slate-700'
            : 'absolute right-0 mt-2 w-56 bg-[#12121A] rounded-lg shadow-xl py-2 z-50 border border-white/10';
    }

    public function dropdownHeaderClass(): string
    {
        return $this->isDarkTenant()
            ? 'px-4 py-3 border-b border-slate-700 bg-slate-800/80'
            : 'px-4 py-3 border-b border-white/10 bg-black/40';
    }

    public function dropdownTitleClass(): string
    {
        return 'text-sm font-semibold text-white';
    }

    public function dropdownItemClass(): string
    {
        return $this->isDarkTenant()
            ? 'block px-4 py-3 text-sm text-slate-200 hover:bg-slate-800 border-b border-slate-800 last:border-b-0 transition-colors'
            : 'block px-4 py-3 text-sm text-slate-200 hover:bg-indigo-500/10 border-b border-white/5 last:border-b-0 transition-colors';
    }

    public function calendarFrameClass(): string
    {
        return $this->accentPick(
            'rounded-3xl bg-gradient-to-br from-yellow-500/25 via-slate-800 to-amber-500/10 p-[1px] shadow-md shadow-black/40',
            'rounded-3xl bg-gradient-to-br from-sky-500/20 via-slate-800 to-cyan-500/10 p-[1px] shadow-md shadow-black/40',
            'rounded-3xl bg-gradient-to-br from-indigo-500/25 via-[#12121A] to-violet-500/15 p-[1px] shadow-md shadow-black/40',
        );
    }

    public function calendarInnerClass(): string
    {
        return $this->accentPick(
            'rounded-[1.35rem] bg-slate-900/95 backdrop-blur-sm p-5 text-left ring-1 ring-yellow-500/20',
            'rounded-[1.35rem] bg-slate-900/95 backdrop-blur-sm p-5 text-left ring-1 ring-sky-500/20',
            'rounded-[1.35rem] bg-[#12121A]/95 backdrop-blur-sm p-5 text-left ring-1 ring-indigo-500/20',
        );
    }

    public function calendarNavButtonClass(): string
    {
        return $this->isDarkTenant()
            ? 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-600 bg-slate-800 text-slate-200 shadow-sm hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-35'
            : 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/10 bg-black text-slate-200 shadow-sm hover:bg-slate-900 disabled:cursor-not-allowed disabled:opacity-35';
    }

    public function calendarMonthTitleClass(): string
    {
        return 'min-w-0 flex-1 text-center text-base font-bold tracking-tight text-white sm:text-lg';
    }

    public function calendarWeekdayClass(): string
    {
        return 'mb-2 grid grid-cols-7 gap-1 text-center text-[10px] font-bold uppercase tracking-wider text-slate-500 sm:text-xs';
    }

    public function calendarDayWithEventsActiveClass(): string
    {
        return $this->accentPick(
            'bg-gradient-to-br from-yellow-500 to-amber-600 text-slate-950 shadow-md ring-2 ring-yellow-400/50',
            'bg-gradient-to-br from-sky-500 to-cyan-600 text-white shadow-md ring-2 ring-sky-400/50',
            'bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-md ring-2 ring-indigo-400/50',
        );
    }

    public function calendarDayWithEventsClass(): string
    {
        return $this->accentPick(
            'bg-gradient-to-b from-slate-800 to-slate-900 text-yellow-100 ring-1 ring-slate-600 hover:from-slate-700',
            'bg-gradient-to-b from-slate-800 to-slate-900 text-sky-100 ring-1 ring-slate-600 hover:from-slate-700',
            'bg-gradient-to-b from-[#1a1a24] to-[#12121A] text-indigo-100 ring-1 ring-indigo-500/30 hover:from-indigo-950',
        );
    }

    public function calendarDayEmptyTodayClass(): string
    {
        return $this->accentPick(
            'bg-slate-800 font-bold text-white ring-2 ring-yellow-500/40',
            'bg-slate-800 font-bold text-white ring-2 ring-sky-500/40',
            'bg-slate-800 font-bold text-white ring-2 ring-indigo-500/40',
        );
    }

    public function calendarDayEmptyClass(): string
    {
        return 'text-slate-400 hover:bg-slate-800/80';
    }

    public function calendarFooterClass(): string
    {
        return $this->isDarkTenant()
            ? 'mt-4 border-t border-slate-700 pt-3 text-center text-xs leading-relaxed text-slate-400'
            : 'mt-4 border-t border-white/10 pt-3 text-center text-xs leading-relaxed text-slate-400';
    }

    public function calendarFooterHintClass(): string
    {
        return $this->accentPick('text-yellow-400/90', 'text-sky-400/90', 'text-indigo-400/90');
    }

    /** Schedule | Map and Artists | Venues toggles on home. */
    public function homeSegmentGroupClass(): string
    {
        return $this->isDarkTenant()
            ? 'inline-flex rounded-xl border border-slate-700 bg-slate-900/80 p-1'
            : 'inline-flex rounded-xl border border-white/10 bg-[#12121A]/80 p-1';
    }

    public function homeSegmentButtonClass(): string
    {
        return 'rounded-lg px-4 py-2 text-sm font-semibold transition-colors duration-200';
    }

    public function homeSegmentActiveClass(): string
    {
        return $this->accentPick(
            'bg-yellow-500 text-slate-950 shadow-sm',
            'bg-sky-500 text-white shadow-sm',
            'bg-indigo-600 text-white shadow-sm',
        );
    }

    public function homeSegmentInactiveClass(): string
    {
        return $this->isDarkTenant()
            ? 'text-slate-300 hover:text-white hover:bg-slate-800'
            : 'text-slate-400 hover:text-white hover:bg-white/5';
    }

    public function coverflowFrameClass(): string
    {
        return $this->accentPick(
            'rounded-2xl border border-yellow-500/20 bg-slate-900/90 shadow-lg shadow-black/30',
            'rounded-2xl border border-sky-500/20 bg-slate-900/90 shadow-lg shadow-black/30',
            'rounded-2xl border border-indigo-500/20 bg-[#12121A]/95 shadow-lg shadow-black/40',
        );
    }

    public function homeFilterChipActiveClass(): string
    {
        return $this->accentPick(
            'bg-yellow-500 text-slate-950 border-yellow-400',
            'bg-sky-500 text-white border-sky-400',
            'bg-indigo-600 text-white border-indigo-500',
        );
    }

    public function homeFilterChipInactiveClass(): string
    {
        return $this->accentPick(
            'border border-slate-600 bg-slate-800/80 text-slate-200 hover:border-yellow-500/50',
            'border border-slate-600 bg-slate-800/80 text-slate-200 hover:border-sky-500/50',
            'border border-white/10 bg-black/40 text-slate-300 hover:border-indigo-500/40',
        );
    }

    public function breadcrumbBarClass(): string
    {
        return $this->isDarkTenant()
            ? 'inline-flex items-center bg-slate-900/90 rounded-lg shadow px-4 py-2 space-x-2 text-xs font-medium text-slate-200 border border-slate-700'
            : 'inline-flex items-center bg-[#12121A]/90 rounded-lg shadow px-4 py-2 space-x-2 text-xs font-medium text-slate-200 border border-white/10';
    }

    public function breadcrumbLinkClass(): string
    {
        return $this->accentPick(
            'flex items-center hover:underline text-yellow-400',
            'flex items-center hover:underline text-sky-400',
            'flex items-center hover:underline text-indigo-400',
        );
    }

    public function formLabelClass(): string
    {
        return 'block text-sm font-medium text-slate-300 mb-2';
    }

    public function formInputClass(): string
    {
        return $this->accentPick(
            'block w-full px-3 py-2 bg-slate-950 border border-slate-600 rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent',
            'block w-full px-3 py-2 bg-slate-950 border border-slate-600 rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent',
            'block w-full px-3 py-2 bg-black border border-white/15 rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
        );
    }

    public function formSelectClass(): string
    {
        return $this->formInputClass();
    }

    public function searchResultsBannerClass(): string
    {
        return $this->accentPick(
            'mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg',
            'mb-6 p-4 bg-sky-500/10 border border-sky-500/30 rounded-lg',
            'mb-6 p-4 bg-indigo-500/10 border border-indigo-500/30 rounded-lg',
        );
    }

    public function searchResultsTitleClass(): string
    {
        return 'text-sm font-medium text-white';
    }

    public function searchResultsTextClass(): string
    {
        return $this->accentPick('text-sm text-yellow-200', 'text-sm text-sky-200', 'text-sm text-indigo-200');
    }

    public function detailPanelClass(string $padding = 'p-6'): string
    {
        $base = $this->isDarkTenant()
            ? 'bg-slate-900 rounded-xl shadow-sm border border-slate-700'
            : 'bg-[#12121A] rounded-xl shadow-sm border border-white/10';

        return trim($base.' '.$padding);
    }

    public function detailPanelHeadingClass(): string
    {
        return 'text-2xl font-bold text-white mb-4';
    }

    public function detailSubheadingClass(): string
    {
        return 'text-xl font-bold text-white mb-4';
    }

    public function detailBodyTextClass(): string
    {
        return 'text-slate-300';
    }

    public function detailMutedTextClass(): string
    {
        return 'text-slate-400';
    }

    public function detailEmphasisTextClass(): string
    {
        return 'font-semibold text-white';
    }

    public function detailInsetClass(): string
    {
        return $this->isDarkTenant()
            ? 'flex items-center p-4 bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors'
            : 'flex items-center p-4 bg-black/40 rounded-lg hover:bg-black/60 transition-colors';
    }

    public function viewSwitcherLabelClass(): string
    {
        return 'text-sm text-slate-400 mr-2';
    }

    public function viewSwitcherActiveClass(): string
    {
        return $this->accentPick(
            'p-2 rounded-lg transition-colors bg-yellow-500/20 text-yellow-300',
            'p-2 rounded-lg transition-colors bg-sky-500/20 text-sky-300',
            'p-2 rounded-lg transition-colors bg-indigo-500/20 text-indigo-300',
        );
    }

    public function viewSwitcherInactiveClass(): string
    {
        return 'p-2 rounded-lg transition-colors text-slate-500 hover:text-slate-200 hover:bg-slate-800';
    }

    public function accentLinkClass(): string
    {
        return $this->accentPick(
            'text-yellow-400 hover:text-yellow-300 px-4 py-2 flex items-center font-medium transition-colors duration-200',
            'text-sky-400 hover:text-sky-300 px-4 py-2 flex items-center font-medium transition-colors duration-200',
            'text-indigo-400 hover:text-indigo-300 px-4 py-2 flex items-center font-medium transition-colors duration-200',
        );
    }

    public function emptyStateTitleClass(): string
    {
        return 'text-lg font-medium text-white mb-2';
    }

    public function emptyStateTextClass(): string
    {
        return 'text-slate-400 mb-6';
    }

    public function tableShellClass(): string
    {
        return $this->isDarkTenant()
            ? 'bg-slate-900 rounded-xl shadow-sm border border-slate-700 overflow-hidden'
            : 'bg-[#12121A] rounded-xl shadow-sm border border-white/10 overflow-hidden';
    }

    public function tableHeadClass(): string
    {
        return $this->isDarkTenant() ? 'bg-slate-800' : 'bg-black/50';
    }

    public function tableHeadCellClass(): string
    {
        return 'px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider';
    }

    public function tableBodyClass(): string
    {
        return $this->isDarkTenant() ? 'divide-y divide-slate-700' : 'divide-y divide-white/10';
    }

    public function tableRowClass(): string
    {
        return $this->isDarkTenant()
            ? 'hover:bg-slate-800/80 transition-colors'
            : 'hover:bg-white/5 transition-colors';
    }

    public function tableCellPrimaryClass(): string
    {
        return 'text-sm font-medium text-white';
    }

    public function tableCellSecondaryClass(): string
    {
        return 'text-sm text-slate-400';
    }

    /** Google Maps JSON style rules — dark palette matched to public site brand. */
    public function googleMapStyles(): array
    {
        $land = $this->isDarkTenant() ? '#0f172a' : '#12121A';
        $landAlt = $this->isDarkTenant() ? '#1e293b' : '#1a1a24';
        $road = $this->isDarkTenant() ? '#334155' : '#2a2a38';
        $roadStroke = $this->isDarkTenant() ? '#1e293b' : '#1e1e28';
        $water = $this->isDarkTenant() ? '#0c4a6e' : '#0c1222';

        return [
            ['elementType' => 'geometry', 'stylers' => [['color' => $land]]],
            ['elementType' => 'labels.text.stroke', 'stylers' => [['color' => $land]]],
            ['elementType' => 'labels.text.fill', 'stylers' => [['color' => '#94a3b8']]],
            ['featureType' => 'administrative.locality', 'elementType' => 'labels.text.fill', 'stylers' => [['color' => '#cbd5e1']]],
            ['featureType' => 'poi', 'elementType' => 'labels.text.fill', 'stylers' => [['color' => '#64748b']]],
            ['featureType' => 'poi.park', 'elementType' => 'geometry', 'stylers' => [['color' => $landAlt]]],
            ['featureType' => 'road', 'elementType' => 'geometry', 'stylers' => [['color' => $road]]],
            ['featureType' => 'road', 'elementType' => 'geometry.stroke', 'stylers' => [['color' => $roadStroke]]],
            ['featureType' => 'road.highway', 'elementType' => 'geometry', 'stylers' => [['color' => '#475569']]],
            ['featureType' => 'transit', 'elementType' => 'geometry', 'stylers' => [['color' => $roadStroke]]],
            ['featureType' => 'water', 'elementType' => 'geometry', 'stylers' => [['color' => $water]]],
            ['featureType' => 'water', 'elementType' => 'labels.text.fill', 'stylers' => [['color' => '#475569']]],
        ];
    }

    public function googleMapStylesJson(): string
    {
        return json_encode($this->googleMapStyles(), JSON_UNESCAPED_UNICODE);
    }

    public function mapFrameClass(): string
    {
        return $this->isDarkTenant()
            ? 'w-full rounded-2xl overflow-hidden shadow-sm border border-slate-700 relative'
            : 'w-full rounded-2xl overflow-hidden shadow-sm border border-white/10 relative';
    }

    public function mapLegendClass(): string
    {
        return $this->isDarkTenant()
            ? 'absolute top-4 left-4 z-10 bg-slate-900 rounded-lg shadow-lg p-3 border border-slate-700'
            : 'absolute top-4 left-4 z-10 bg-[#12121A] rounded-lg shadow-lg p-3 border border-white/10';
    }

    public function mapLoadingClass(): string
    {
        return $this->isDarkTenant()
            ? 'absolute inset-0 w-full h-full flex items-center justify-center bg-slate-950'
            : 'absolute inset-0 w-full h-full flex items-center justify-center bg-black';
    }

    public function mapFilterActiveClass(): string
    {
        return $this->accentPick(
            'time-filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 bg-yellow-400 text-slate-950 shadow-md hover:shadow-lg',
            'time-filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 bg-sky-400 text-slate-950 shadow-md hover:shadow-lg',
            'time-filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 bg-indigo-500 text-white shadow-md hover:shadow-lg',
        );
    }

    public function mapFilterInactiveClass(): string
    {
        return $this->accentPick(
            'time-filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 bg-slate-800 text-slate-200 border border-slate-600 hover:border-yellow-400 hover:bg-slate-700',
            'time-filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 bg-slate-800 text-slate-200 border border-slate-600 hover:border-sky-400 hover:bg-slate-700',
            'time-filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 bg-black text-slate-200 border border-white/15 hover:border-indigo-400 hover:bg-slate-900',
        );
    }

    /** Primary marker / pin colour for inline SVG map icons. */
    public function mapMarkerHex(): string
    {
        if ($this->isFm919) {
            return '#f2c200';
        }

        return $this->isRogues ? '#0ea5e9' : '#6366f1';
    }

    /** User-location dot colour on the map. */
    public function mapUserLocationHex(): string
    {
        if ($this->isFm919) {
            return '#fde047';
        }

        return $this->isRogues ? '#38bdf8' : '#818cf8';
    }

    public function authPageClass(): string
    {
        return $this->pageContentShellClass().' flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8';
    }

    public function authCardClass(): string
    {
        return $this->detailPanelClass('p-8').' rounded-2xl';
    }

    public function authHeaderIconWrapClass(): string
    {
        return $this->accentPick(
            'bg-gradient-to-r from-yellow-500 to-amber-500 p-3 rounded-xl shadow-sm',
            'bg-gradient-to-r from-sky-500 to-cyan-500 p-3 rounded-xl shadow-sm',
            'bg-gradient-to-r from-indigo-600 to-violet-600 p-3 rounded-xl shadow-sm',
        );
    }

    public function authInputClass(): string
    {
        return $this->accentPick(
            'block w-full pl-10 pr-3 py-3 bg-slate-950 border border-slate-600 rounded-xl text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent',
            'block w-full pl-10 pr-3 py-3 bg-slate-950 border border-slate-600 rounded-xl text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent',
            'block w-full pl-10 pr-3 py-3 bg-black border border-white/15 rounded-xl text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
        );
    }

    public function authSelectClass(): string
    {
        return $this->authInputClass();
    }

    public function authIconClass(): string
    {
        return $this->accentPick('h-5 w-5 text-yellow-400', 'h-5 w-5 text-sky-400', 'h-5 w-5 text-indigo-400');
    }

    public function authSecondaryButtonClass(): string
    {
        return $this->accentPick(
            'w-full flex items-center justify-center gap-3 py-3 px-4 border border-slate-600 rounded-xl shadow-sm text-sm font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all duration-300',
            'w-full flex items-center justify-center gap-3 py-3 px-4 border border-slate-600 rounded-xl shadow-sm text-sm font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-400 transition-all duration-300',
            'w-full flex items-center justify-center gap-3 py-3 px-4 border border-white/15 rounded-xl shadow-sm text-sm font-medium text-slate-200 bg-black hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-300',
        );
    }

    public function authDividerLineClass(): string
    {
        return $this->isDarkTenant() ? 'border-slate-700' : 'border-white/10';
    }

    public function authDividerLabelClass(): string
    {
        return $this->isDarkTenant()
            ? 'px-2 bg-slate-900 text-slate-400'
            : 'px-2 bg-[#12121A] text-slate-400';
    }

    public function authCheckboxClass(): string
    {
        return $this->accentPick(
            'h-4 w-4 text-yellow-400 focus:ring-yellow-400 border-slate-600 rounded bg-slate-950',
            'h-4 w-4 text-sky-400 focus:ring-sky-400 border-slate-600 rounded bg-slate-950',
            'h-4 w-4 text-indigo-500 focus:ring-indigo-500 border-white/20 rounded bg-black',
        );
    }

    /** Authenticated user dashboard / profile outer wrapper. */
    public function dashboardShellClass(): string
    {
        return $this->pageContentShellClass().' dashboard-shell min-h-screen';
    }

    public function footerCopyrightLine(): string
    {
        if ($this->isPartnerTenant()) {
            return $this->name.' · My Gig Guide';
        }

        return 'My Gig Guide';
    }

    public function footerAccentLinkClass(): string
    {
        return $this->accentPick(
            'text-yellow-400 hover:text-white',
            'text-sky-400 hover:text-white',
            'text-purple-400 hover:text-white',
        );
    }

    public function footerBrandTitle(): string
    {
        if ($this->isDarkTenant()) {
            return $this->name;
        }

        return 'My Gig Guide';
    }

    /** Google Play listing for the My Gig Guide Android app (vanilla site only). */
    public function androidPlayStoreUrl(): ?string
    {
        if ($this->isPartnerTenant()) {
            return null;
        }

        $url = config('app.android_play_store_url');

        return is_string($url) && $url !== '' ? $url : null;
    }
}
