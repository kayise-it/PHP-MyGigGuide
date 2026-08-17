function parseYmd(str) {
    const [y, m, d] = str.split('-').map(Number);
    return new Date(y, m - 1, d);
}

function formatYmd(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function monthStart(year, month) {
    return new Date(year, month, 1);
}

/**
 * Alpine factory for the home gig calendar (Schedule tab).
 */
export function homeEventCalendarData(calendarByDate, rangeStartStr, rangeEndStr, eventsIndexUrl) {
    const rangeStart = parseYmd(rangeStartStr);
    const rangeEnd = parseYmd(rangeEndStr);
    const initial = parseYmd(rangeStartStr);

    return {
        calendarByDate: calendarByDate ?? {},
        viewYear: initial.getFullYear(),
        viewMonth: initial.getMonth(),
        rangeStart,
        rangeEnd,
        eventsIndexUrl,

        get monthLabel() {
            const d = new Date(this.viewYear, this.viewMonth, 1);
            return d.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        },

        get canPrev() {
            const view = monthStart(this.viewYear, this.viewMonth);
            return view > monthStart(this.rangeStart.getFullYear(), this.rangeStart.getMonth());
        },

        get canNext() {
            const view = monthStart(this.viewYear, this.viewMonth);
            return view < monthStart(this.rangeEnd.getFullYear(), this.rangeEnd.getMonth());
        },

        get gridCells() {
            const year = this.viewYear;
            const month = this.viewMonth;
            const first = new Date(year, month, 1);
            const last = new Date(year, month + 1, 0);
            let startDow = first.getDay();
            startDow = startDow === 0 ? 6 : startDow - 1;

            const cells = [];
            for (let i = 0; i < startDow; i++) {
                cells.push({ placeholder: true });
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            for (let day = 1; day <= last.getDate(); day++) {
                const d = new Date(year, month, day);
                const key = formatYmd(d);
                const eventCount = (this.calendarByDate[key] ?? []).length;
                const listUrl =
                    eventCount > 0
                        ? `${this.eventsIndexUrl}?date_from=${key}&date_to=${key}`
                        : '#';

                cells.push({
                    placeholder: false,
                    day,
                    eventCount,
                    listUrl,
                    isToday: d.getTime() === today.getTime(),
                });
            }

            return cells;
        },

        prevMonth() {
            if (!this.canPrev) return;
            if (this.viewMonth === 0) {
                this.viewYear -= 1;
                this.viewMonth = 11;
            } else {
                this.viewMonth -= 1;
            }
        },

        nextMonth() {
            if (!this.canNext) return;
            if (this.viewMonth === 11) {
                this.viewYear += 1;
                this.viewMonth = 0;
            } else {
                this.viewMonth += 1;
            }
        },
    };
}
