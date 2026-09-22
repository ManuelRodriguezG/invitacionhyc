(function ($) {
    "use strict";

    var musicAudio = null;
    var musicButton = null;

    function setMusicButton(isPlaying) {
        if (!musicButton) {
            return;
        }

        var icon = musicButton.querySelector("i");
        var label = musicButton.querySelector("span");

        musicButton.classList.toggle("is-playing", isPlaying);
        musicButton.setAttribute("aria-pressed", isPlaying ? "true" : "false");
        musicButton.setAttribute("aria-label", isPlaying ? "Pausar musica" : "Reproducir musica");

        if (icon) {
            icon.className = isPlaying ? "fa fa-pause" : "fa fa-play";
        }

        if (label) {
            label.textContent = isPlaying ? "Pausar" : "Musica";
        }
    }

    function playWeddingMusic() {
        if (!musicAudio || !musicAudio.paused) {
            return;
        }

        musicAudio.play().then(function () {
            setMusicButton(true);
        }).catch(function () {
            setMusicButton(false);
        });
    }

    function renderCountdown() {
        var clock = $("#clock");
        var dateValue = $("body").data("wedding-date");

        if (!clock.length || !dateValue) {
            return;
        }

        var target = new Date(dateValue).getTime();

        function update() {
            var distance = target - Date.now();

            if (distance <= 0) {
                clock.html('<div class="box"><div><div class="time">00</div><span>Dias</span></div></div><div class="box"><div><div class="time">00</div><span>Horas</span></div></div><div class="box"><div><div class="time">00</div><span>Min</span></div></div><div class="box"><div><div class="time">00</div><span>Seg</span></div></div>');
                return;
            }

            var days = Math.floor(distance / 86400000);
            var hours = Math.floor((distance % 86400000) / 3600000);
            var minutes = Math.floor((distance % 3600000) / 60000);
            var seconds = Math.floor((distance % 60000) / 1000);

            clock.html(
                '<div class="box"><div><div class="time">' + days + '</div><span>Dias</span></div></div>' +
                '<div class="box"><div><div class="time">' + String(hours).padStart(2, "0") + '</div><span>Horas</span></div></div>' +
                '<div class="box"><div><div class="time">' + String(minutes).padStart(2, "0") + '</div><span>Min</span></div></div>' +
                '<div class="box"><div><div class="time">' + String(seconds).padStart(2, "0") + '</div><span>Seg</span></div></div>'
            );
        }

        update();
        window.setInterval(update, 1000);
    }

    function bindSinglePageNav() {
        $('.navigation-holder a[href^="#"], .wedding-footer a[href^="#"], .wedding-actions a[href^="#"]').on("click", function () {
            $(".navigation-holder").removeClass("slideInn");
            $("body").removeClass("body-overlay");
        });
    }

    function bindRsvp() {
        $("#rsvp-form").on("submit", function (event) {
            event.preventDefault();

            var formData = new FormData(this);
            var payload = {};

            formData.forEach(function (value, key) {
                payload[key] = value;
            });

            try {
                var saved = JSON.parse(window.localStorage.getItem("wedding_rsvp") || "[]");
                saved.push({
                    createdAt: new Date().toISOString(),
                    data: payload
                });
                window.localStorage.setItem("wedding_rsvp", JSON.stringify(saved));
            } catch (error) {
                window.console.warn("No fue posible guardar RSVP localmente", error);
            }

            $("#rsvp-message")
                .addClass("is-visible")
                .text("Gracias. Tu confirmacion quedo registrada en esta version local.");

            this.reset();
            $("#attend").prop("checked", true);
        });
    }

    function bindMusicPlayer() {
        musicAudio = document.getElementById("wedding-song");
        musicButton = document.querySelector(".wedding-music-toggle");

        if (!musicAudio || !musicButton) {
            return;
        }

        musicAudio.addEventListener("error", function () {
            musicButton.classList.add("is-unavailable");
            musicButton.setAttribute("aria-label", "Cancion pendiente");
            musicButton.disabled = true;

            var icon = musicButton.querySelector("i");
            var label = musicButton.querySelector("span");

            if (icon) {
                icon.className = "fa fa-music";
            }

            if (label) {
                label.textContent = "Cancion pendiente";
            }
        });

        musicButton.addEventListener("click", function () {
            if (musicAudio.paused) {
                playWeddingMusic();
            } else {
                musicAudio.pause();
                setMusicButton(false);
            }
        });
    }

    function bindInvitationCover() {
        var cover = document.getElementById("wedding-cover");
        var button = document.getElementById("open-invitation");

        if (!cover || !button) {
            return;
        }

        button.addEventListener("click", function () {
            cover.classList.add("is-hidden");
            cover.setAttribute("aria-hidden", "true");
            playWeddingMusic();
        });
    }

    $(function () {
        renderCountdown();
        bindSinglePageNav();
        bindRsvp();
        bindMusicPlayer();
        bindInvitationCover();
    });
})(jQuery);
