import $ from 'jquery';

class Search {
    constructor() {
        this.addSearchHTML();
        this.resultsDiv = $("#search-overlay__results");
        this.openButton = $(".js-search-trigger");
        this.closeButton = $(".search-overlay__close");
        this.searchOverlay = $(".search-overlay");
        this.searchField = $("#search-term");
        this.events();
        this.isOverlayOpen = false;
        this.isSpinnerVisible = false;
        this.previousValue;
        this.typingTimer;
    }

    events() {
        this.openButton.on("click", this.openOverlay.bind(this));
        this.closeButton.on("click", this.closeOverlay.bind(this));
        $(document).on("keydown", this.keyPressDispatcher.bind(this));
        this.searchField.on("keyup", this.typingLogic.bind(this));
    }



    typingLogic() {
        if (this.searchField.val() != this.previousValue) {
            clearTimeout(this.typingTimer);
            if (this.searchField.val()) {
                if (!this.isSpinnerVisible) {
                    this.resultsDiv.html('<div class="spinner-loader"></div>');
                    this.isSpinnerVisible = true;
                }
                this.typingTimer = setTimeout(this.getResults.bind(this), 750);
            } else {
                this.resultsDiv.html('');
                this.isSpinnerVisible = false;
            }
        }
        this.previousValue = this.searchField.val();
    }

    getResults() {
        $.getJSON(universityData.root_url + '/wp-json/university/v1/search?term=' + this.searchField.val(), (results) => {
            this.resultsDiv.html(`
                <div class="row">
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">General Information</h2>
                        ${results.generalInfo.length ? '<ul class="link-list min-list">' : '<p>No general info matches the search string.</p>'}
                            ${results.generalInfo.map(item => `
                                <li><a href="${item.url}">${item.title}</a>${item.postType == 'post' ? ' post by ' + item.authorName : ''}</li>`).join('')}
                        ${results.generalInfo.length ? '</ul>' : ''}
                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Programs</h2>
                        ${results.program.length ? '<ul class="link-list min-list">' : `<p>No programs that match the search string.<br><a href="${universityData.root_url}/programs">All programs</a></p>`}
                            ${results.program.map(item => `<li><a href="${item.url}">${item.title}</a></li>`).join('')}
                        ${results.program.length ? '</ul>' : ''}
                        <h2 class="search-overlay__section-title">Professors</h2>
                        ${results.professor.length ? '<ul class="professor-cards">' : '<p>No professors that match the search string.</p>'}
                            ${results.professor.map(item => `
                                <li class="professor-card__list-item">
                                    <a class="professor-card" href="${item.url}">
                                        <img class="professor-card__image" src="${item.image}" alt="">
                                        <span class="professor-card__name">${item.title}</span>
                                    </a>
                                </li>
                                `).join('')}
                        ${results.professor.length ? '</ul>' : ''}
                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Campuses</h2>
                        ${results.campus.length ? '<ul class="link-list min-list">' : `<p>No Campuses match the search string.<br><a href="${universityData.root_url}/campuses">All Campuses</a></p>`}
                            ${results.campus.map(item => `<li><a href="${item.url}">${item.title}</a></li>`).join('')}
                        ${results.campus.length ? '</ul>' : ''}
                        <h2 class="search-overlay__section-title">Events</h2>
                        ${results.event.length ? '' : '<p>No events that matches the search string<br><a href="${universityData.root_url}/events">View all events</a></p>'}
                            ${results.event.map(item => `
                                <div class="event-summary">
                                    <a class="event-summary__date t-center" href="${item.url}">
                                        <span class="event-summary__month">${item.month}</span>
                                        <span class="event-summary__day">${item.day}</span>
                                    </a>
                                    <div class="event-summary__content">
                                        <h5 class="event-summary__title headline headline--tiny"><a href="${item.url}">${item.title}</a></h5>
                                        <p>${item.description}<a href="${item.url}" class="nu gray">Learn more</a></p>
                                    </div>
                                </div>
                            `).join('')}
                    </div>
                </div>
            `);
            this.isSpinnerVisible = false;
        })
    }

    keyPressDispatcher(e) {
        
        if (e.keyCode == 83 && !this.isOverlayOpen && !$("input, textarea".is(':focus'))) {
            this.openOverlay();
        }
        if (e.keyCode == 27 && this.isOverlayOpen) {
            this.closeOverlay();
        }
    }

    openOverlay() {
        this.searchOverlay.addClass("search-overlay--active");
        $("body").addClass("body-no-scroll");
        this.searchField.val('');
        setTimeout(() => this.searchField.focus(), 301);
        this.isOverlayOpen = true;
        return false;
    }

    closeOverlay() {
        this.searchOverlay.removeClass("search-overlay--active");
        $("body").removeClass("body-no-scroll");
        this.isOverlayOpen = false;
    }

    addSearchHTML() {
        $("body").append(`
            <div class="search-overlay">
                <div class="search-overlay__top">
                    <div class="container">
                        <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
                        <input 
                        type="text"
                        class="search-term"
                        placeholder="What are you looking for?"
                        id="search-term"
                        autocomplete="off"
                        >
                        <i class="fa fa-window-close search-overlay__close"></i>
                    </div>
                </div>
                <div class="container">
                <div id="search-overlay__results"></div>
                </div>
            </div>
        `);
    }
}

export default Search;