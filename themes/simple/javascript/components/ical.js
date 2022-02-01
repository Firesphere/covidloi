import makeUrl, { TCalendarEvent } from 'add-event-to-calendar';

const inputdate = document.getElementById('booster-date');
const inputlocation = document.getElementById('booster-location');
const button = document.getElementById('get-links');
const calLinks = Array.from(document.getElementsByClassName('calendar-link'));
const calendarlinks = document.getElementById('booster-links');
const boosterdate = document.getElementById('booster-plan');
let value = new Date();

Date.isLeapYear = (year) => {
    return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0));
};

Date.getDaysInMonth = (year, month) => {
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
    let dayvalue = 0;
    if (days === 0) {
        dayvalue = 1;
    }
    if (days === 6) {
        dayvalue = 2;
    }
    this.setDate(this.getDate() + dayvalue);
    return this;

}

const getCalendarEvent = TCalendarEvent => ({
    name: `Booster shot`,
    location: inputlocation.value,
    details: `Booster shot for Covid-19`,
    startsAt: value,
    endsAt: value
});

button.addEventListener('click', () => {
    value = new Date(inputdate.value).addMonths(4).updateWeekend();
    let links = makeUrl(getCalendarEvent());
    calLinks.forEach((item) => {
        let type = item.getAttribute('data-type');
        item.setAttribute('href', links[type]);
    });
    boosterdate.innerText = value.toDateString();
    calendarlinks.classList.remove('hidden');
});
