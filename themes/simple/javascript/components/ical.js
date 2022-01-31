import makeUrl, { TCalendarEvent } from 'add-event-to-calendar';

const inputdate = document.getElementById('booster-date');
const inputlocation = document.getElementById('booster-location');
const button = document.getElementById('get-links');
const googlelink = document.getElementById('google-link');
const outlooklink = document.getElementById('outlook-link');
const yahoolink = document.getElementById('yahoo-link');
const icslink = document.getElementById('ics-link');
const calendarlinks = document.getElementById('booster-links');
let calcdate;

Date.isLeapYear = function (year) {
    return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0));
};

Date.getDaysInMonth = function (year, month) {
    return [31, (Date.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
};

Date.prototype.isLeapYear = function () {
    return Date.isLeapYear(this.getFullYear());
};

Date.prototype.getDaysInMonth = function () {
    return Date.getDaysInMonth(this.getFullYear(), this.getMonth());
};

Date.prototype.addMonths = function (value) {
    let n = this.getDate();
    this.setDate(1);
    this.setMonth(this.getMonth() + value);
    this.setDate(Math.min(n, this.getDaysInMonth()));
    return this;
};

Date.prototype.updateWeekend = function () {
    let days = this.getDay();
    let value = 0;
    if (days === 0) {
        value = 1;
    }
    if (value === 6) {
        value = 2;
    }
    this.setDate(this.getDate() + value);
    return this;

}

const getCalendarEvent = TCalendarEvent => ({
    name: `Booster shot`,
    location: inputlocation.value,
    details: `Booster shot for Covid-19`,
    startsAt: updateDate(),
    endsAt: updateDate()
});

const updateDate = () => {
    let value = inputdate.value;
    return new Date(value).addMonths(4).updateWeekend();
}

button.addEventListener('click', () => {
    let links = makeUrl(getCalendarEvent());
    googlelink.setAttribute('href', links.google);
    yahoolink.setAttribute('href', links.yahoo);
    outlooklink.setAttribute('href', links.outlook);
    icslink.setAttribute('href', links.ics);
    calendarlinks.classList.remove('hidden');
});
