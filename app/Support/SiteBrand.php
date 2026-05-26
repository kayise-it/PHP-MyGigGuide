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
    ) {}

    public static function current(?Request $request = null): self
    {
        $request ??= request();
        $host = strtolower($request->getHost());

        if ($host === 'rogues.mygigguide.co.za' || str_starts_with($host, 'rogues.')) {
            return self::rogues();
        }

        return self::mygigguide();
    }

    public static function mygigguide(): self
    {
        return new self(
            key: 'mygigguide',
            name: 'My Gig Guide',
            tagline: 'Discover amazing events, artists, and venues in your area.',
            logoPath: 'logos/logo1.jpeg',
            faviconPath: 'logos/favicon.png',
            isRogues: false,
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
        );
    }

    public function logoUrl(): string
    {
        return asset($this->logoPath);
    }

    public function faviconUrl(): string
    {
        return asset($this->faviconPath);
    }

    public function bodyClass(): string
    {
        return $this->isRogues ? 'brand-rogues' : 'brand-mygigguide';
    }

    public function navBarClass(): string
    {
        return $this->isRogues
            ? 'site-nav bg-slate-900 border-b border-slate-800 shadow-lg'
            : 'site-nav bg-white shadow-sm border-b border-gray-200';
    }

    public function navTitleClass(): string
    {
        return $this->isRogues
            ? 'text-xl font-bold text-white whitespace-nowrap'
            : 'text-xl font-bold text-gray-900 whitespace-nowrap';
    }

    public function navLinkClass(bool $active): string
    {
        if ($this->isRogues) {
            return $active
                ? 'flex items-center text-sky-400 bg-slate-800 px-3 py-2.5 rounded-xl text-sm font-medium border border-sky-500/40 shadow-sm'
                : 'flex items-center text-slate-300 px-3 py-2.5 rounded-xl text-sm font-medium border border-transparent hover:bg-slate-800 hover:text-white hover:shadow-sm transition-all duration-200';
        }

        return $active ? 'nav-link-active' : 'nav-link';
    }

    public function mobileNavPanelClass(): string
    {
        return $this->isRogues
            ? 'md:hidden bg-slate-900 border-t border-slate-800'
            : 'md:hidden bg-white border-t border-gray-200';
    }

    public function mobileNavLinkClass(bool $active): string
    {
        if ($this->isRogues) {
            return $active
                ? 'block px-3 py-2 text-base font-medium text-sky-400 bg-slate-800 rounded-md'
                : 'block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-md transition-colors duration-200';
        }

        return $active ? 'mobile-nav-link-active' : 'mobile-nav-link';
    }

    public function footerBrandTitle(): string
    {
        return $this->isRogues ? $this->name : 'My Gig Guide';
    }
}
