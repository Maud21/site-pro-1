(function (window) {
    
        var BehaviourChecker = function(params) {

            var detectBlacklist = function (strToCheck) {
                var strToCheck = strToCheck.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                for (var id in adwKeywords) {
                    var keywordsCollection = adwKeywords[id];
                    if (keywordsCollection.keyword === 'blackList') {
                        var keywordsList = keywordsCollection.associated_keywords.split(',');
                        for (var j = 0; j < keywordsList.length; j++) {
                            var keyword = keywordsList[j].trim();
                            if (keyword !== '') {
                                var re = new RegExp(keyword.normalize("NFD").replace(/[\u0300-\u036f]/g, ""), 'i');
                                if (strToCheck.match(re)) {
                                    return true;
                                }
                            }
                        }
                    }
                }
                return false;
            };

            var blacklistDetected = '';
            if (typeof adwKeywords !== 'undefined' && adwKeywords !== null) {
                try {
                    if (typeof WIN != "undefined")
                        blacklistDetected = detectBlacklist(WIN.document.title);
                    else
                        blacklistDetected = detectBlacklist(window.document.title);
                } catch (e) {}
                /**Digiteka**/
                try {
                    blacklistDetected = detectBlacklist(window.parent.dtkPlayer.vinfos.video.title);
                } catch (e) {}
                /**fin Digiteka**/
            }
            //           console.log("blacklistDetected", blacklistDetected);
            if (blacklistDetected)
                return false;

            for (var param in params) {
                this[param] = params[param];
            }

            if (!this.creation.creation_use_visibility &&
                !this.creation.creation_minwidth &&
                !this.creation.creation_startat &&
                !this.creation.creation_prevent_incontent &&
                !this.creation.creation_minduration &&
                !this.creation.creation_autoplay_allowed) {
                this.cb.call();
                if (this.creation.creation_repeat > 0) {
                    setTimeout(function() {
                        that.repeatCb.call(that);
                        var checkBehaviour = new BehaviourChecker(params);
                    }, this.creation.creation_repeat * 1000);
                }
                return true;
            }
            var that = this;
            this.cbLaunched = false;

            this.behaviours = new Object();
            if (this.playerIdentity.length > 1 && typeof this.playerIdentity[1].options !== 'undefined' && typeof this.playerIdentity[1].options.s2p !== 'undefined' && this.playerIdentity[1].options.s2p !== null)
                this.s2p = this.playerIdentity[1].options.s2p;
            else
                this.s2p = new adways.interactive.SceneControllerWrapper();
            if (this.playerIdentity.length > 1 && typeof this.playerIdentity[1].options !== 'undefined' && typeof this.playerIdentity[1].options.p2s !== 'undefined' && this.playerIdentity[1].options.p2s !== null)
                this.p2s = this.playerIdentity[1].options.p2s;
            else
                this.p2s = new adways.interactive.SceneControllerWrapper();
            if (this.playerIdentity.length > 1 && typeof this.playerIdentity[1].options !== 'undefined' && typeof this.playerIdentity[1].options.delegate !== 'undefined' && this.playerIdentity[1].options.delegate !== null)
                this.delegate = this.playerIdentity[1].options.delegate;
            else
                this.delegate = eval("new " + delegateParams[this.playerIdentity[0]] + "(this.p2s, this.s2p, this.playerIdentity[1])");

            this.tryLaunchCB = function(trigger) {
                for (var j = 0; j < trigger.dependencies.length; j++) {
                    if (that.behaviours[trigger.dependencies[j]]) {
                        that.behaviours[trigger.dependencies[j]].reset();
                    }
                }
                for (var j = 0; j < trigger.dependencies.length; j++) {
                    if (that.behaviours[trigger.dependencies[j]]) {
                        that.behaviours[trigger.dependencies[j]].reCheck(false);
                    }
                }
                for (var ind in that.behaviours) {
                    if (!that.behaviours[ind].isChecked)
                        return false;
                }
                for (var ind in that.behaviours) {
                    if (!that.behaviours[ind].isValid)
                        return false;
                }
                //            try {
                //                if (typeof adways != "undefined" && that.delegate != null) {
                //                    adways.destruct(that.delegate);
                //                    that.delegate = null;
                //                    //                    delete adways;
                //                }
                //            } catch (e) {
                //                console.log(e, "adw destroy delegate");
                //            }
                if (!that.cbLaunched) {
                    for (var ind in that.behaviours) {
                        that.behaviours[ind].reset();
                    }
                    that.cbLaunched = true;
                    that.cb.call(that, that.delegate, that.s2p, that.p2s);
                    if (this.creation.creation_repeat > 0) {
                        setTimeout(function() {
                            that.repeatCb.call(that);
                            var checkBehaviour = new BehaviourChecker(params);
                        }, this.creation.creation_repeat * 1000);
                    }
                }
                return true;
            };

            if (this.creation.creation_autoplay_allowed) {
                this.behaviours["autoplayallowed"] = new AutoplayAllowedBehaviour({
                    "s2p": this.s2p,
                    "p2s": this.p2s,
                    "delegate": this.delegate,
                    "creation": this.creation,
                    "cb": this.tryLaunchCB,
                    "dependencies": [],
                    "behaviours": this.behaviours
                });
            }
            if (this.creation.creation_prevent_incontent) {
                this.behaviours["preventincontent"] = new PreventinContentBehaviour({
                    "s2p": this.s2p,
                    "p2s": this.p2s,
                    "delegate": this.delegate,
                    "creation": this.creation,
                    "cb": this.tryLaunchCB,
                    "dependencies": [],
                    "behaviours": this.behaviours
                });
            }
            if (this.creation.creation_minduration) {
                this.behaviours["minduration"] = new MinDurationBehaviour({
                    "s2p": this.s2p,
                    "p2s": this.p2s,
                    "delegate": this.delegate,
                    "creation": this.creation,
                    "cb": this.tryLaunchCB,
                    "dependencies": [],
                    "behaviours": this.behaviours
                });
            }
            if (this.creation.creation_use_visibility) {
                this.behaviours["visibility"] = new UseVisibilityBehaviour({
                    "s2p": this.s2p,
                    "p2s": this.p2s,
                    "delegate": this.delegate,
                    "creation": this.creation,
                    "cb": this.tryLaunchCB,
                    "dependencies": ["minwidth"],
                    "behaviours": this.behaviours,
                    "playerIdentity": this.playerIdentity
                });
            }
            if (this.creation.creation_minwidth) {
                this.behaviours["minwidth"] = new MinWidthBehaviour({
                    "s2p": this.s2p,
                    "p2s": this.p2s,
                    "delegate": this.delegate,
                    "creation": this.creation,
                    "cb": this.tryLaunchCB,
                    "dependencies": ["minduration", "visibility", "startat"],
                    "behaviours": this.behaviours
                });
            }
            if (this.creation.creation_startat) {
                this.behaviours["startat"] = new StartAtBehaviour({
                    "s2p": this.s2p,
                    "p2s": this.p2s,
                    "delegate": this.delegate,
                    "creation": this.creation,
                    "cb": this.tryLaunchCB,
                    "dependencies": ["minwidth", "visibility", "minduration"],
                    "behaviours": this.behaviours
                });
            }
            for (var ind in this.behaviours) {
                this.behaviours[ind].reCheck();
            }
            // this.tryLaunchCB();
        };

        AutoplayAllowedBehaviour = function(params) {
            for (var param in params) {
                this[param] = params[param];
            }
            var that = this;
            this.isChecked = false;
            this.isValid = false;
            this.video = document.createElement('video');
            this.video.src = '//videos.adpaths.com/000_v3_encode/video_16x16.mp4';
            this.video.muted = true;
            this.video.setAttribute('webkit-playsinline', true);
            this.video.setAttribute('playsinline', true);
            this.checkAutoplay = function() {
                that.isChecked = true;
                var promise = that.video.play();
                if (promise !== undefined) {
                    promise.then(_ => {
                        //                    console.log("behaviour promise done");
                        that.isValid = true;
                        that.cb(that);
                    }).catch(error => {
                        //                    console.log("behaviour promise error");
                        that.cb(that);
                    });
                } else {
                    //                console.log("behaviour promise undefined");
                    that.cb(that);
                }
            };
            //this.checkAutoplay();
        };

        AutoplayAllowedBehaviour.prototype.reset = function() {
            this.isChecked = false;
            this.isValid = false;
        };

        AutoplayAllowedBehaviour.prototype.reCheck = function() {
            this.checkAutoplay();
        };

        StartAtBehaviour = function(params) {
            for (var param in params) {
                this[param] = params[param];
            }
            var that = this;
            this.checkDepedencies = true;
            this.isChecked = false;
            this.isValid = false;
            this.currentTimeChangedListener = function() {
                if (isNaN(that.p2s.getCurrentTime().valueOf())) {
                    return;
                }
                //console.log(that.p2s.getCurrentTime().valueOf());
                switch (that.creation.creation_startat_type) {
                    case 'second':
                        if (that.p2s.getCurrentTime().valueOf() < that.creation.creation_startat) {
                            return;
                        }
                        break;
                    case 'percent':
                        if (isNaN(that.p2s.getDuration().valueOf()) ||
                            ((that.p2s.getCurrentTime().valueOf() / that.p2s.getDuration().valueOf()) * 100) < that.creation.creation_startat) {
                            return;
                        }
                        break;
                    case 'second_from_the_end':
                        if (isNaN(that.p2s.getDuration().valueOf()) ||
                            ((that.p2s.getDuration().valueOf() - that.p2s.getCurrentTime().valueOf()) > that.creation.creation_startat)) {
                            return;
                        }
                        break;
                        //                    case 'percent_from_the_end':
                        //                        if (isNaN(that.p2s.getDuration().valueOf()) ||
                        //                            ( ((1-(that.p2s.getCurrentTime().valueOf() / that.p2s.getDuration().valueOf()))*100) > that.creation.creation_startat)) {
                        //                            return;
                        //                        }
                        //                        break;
                }
                that.p2s.removeEventListener(adways.resource.events.CURRENT_TIME_CHANGED, that.currentTimeChangedListener);
                that.isValid = true;
                that.isChecked = true;
//                console.log("StartAtBehaviour", that.checkDepedencies);
                if (that.checkDepedencies)
                    that.cb(that);
            };
            //this.p2s.addEventListener(adways.resource.events.CURRENT_TIME_CHANGED, this.currentTimeChangedListener);
            //this.currentTimeChangedListener();
        };

        StartAtBehaviour.prototype.reset = function() {
            this.isChecked = false;
            this.isValid = false;
            this.p2s.removeEventListener(adways.resource.events.CURRENT_TIME_CHANGED, this.currentTimeChangedListener);
        };

        StartAtBehaviour.prototype.reCheck = function() {
            this.checkDepedencies = arguments.length > 0 ? arguments[0] : true;
            this.p2s.addEventListener(adways.resource.events.CURRENT_TIME_CHANGED, this.currentTimeChangedListener);
            this.currentTimeChangedListener();
            this.checkDepedencies = true;
        };

        MinWidthBehaviour = function(params) {
            for (var param in params) {
                this[param] = params[param];
            }
            var that = this;
            this.checkDepedencies = true;
            this.isChecked = false;
            this.isValid = false;
            this.currentTimeChangedListener = function() {
                //console.log(that.p2s.getPlayerSize());
                if (isNaN(that.p2s.getCurrentTime().valueOf()) || that.p2s.getCurrentTime().valueOf() == 0) {
                    return;
                } else if (that.p2s.getCurrentTime().valueOf() >= 1) {
                    if (!isNaN(that.p2s.getPlayerSize()[0]) && that.p2s.getPlayerSize()[0] >= that.creation.creation_minwidth) {
                        that.isValid = true;
                    }
                    that.p2s.removeEventListener(adways.resource.events.CURRENT_TIME_CHANGED, that.currentTimeChangedListener);
                    that.isChecked = true;
//                console.log("MinWidthBehaviour", that.checkDepedencies);
                    if (that.checkDepedencies)
                        that.cb(that);
                }
            };
            this.playerSizeChangedListener = function() {
                that.isChecked = true;
                //console.log(that.p2s.getPlayerSize());
                if (isNaN(that.p2s.getCurrentTime().valueOf()) || that.p2s.getCurrentTime().valueOf() == 0) {
                    return;
                } else if (that.p2s.getCurrentTime().valueOf() >= 1) {
                    if (!isNaN(that.p2s.getPlayerSize()[0]) && that.p2s.getPlayerSize()[0] >= that.creation.creation_minwidth) {
                        that.isValid = true;
                        that.p2s.removeEventListener(adways.resource.events.CURRENT_TIME_CHANGED, that.currentTimeChangedListener);
                        if (that.checkDepedencies)
                            that.cb(that);
                    }
                }
            };
        };

        MinWidthBehaviour.prototype.reset = function() {
            this.isChecked = false;
            this.isValid = false;
            //            this.p2s.removeEventListener(adways.resource.events.CURRENT_TIME_CHANGED, this.currentTimeChangedListener);
            this.p2s.removeEventListener(adways.resource.events.PLAYER_SIZE_CHANGED, this.playerSizeChangedListener);
        };

        MinWidthBehaviour.prototype.reCheck = function() {
            this.checkDepedencies = arguments.length > 0 ? arguments[0] : true;
            //            this.p2s.addEventListener(adways.resource.events.CURRENT_TIME_CHANGED, this.currentTimeChangedListener);
            //            this.currentTimeChangedListener();
            this.p2s.addEventListener(adways.resource.events.PLAYER_SIZE_CHANGED, this.playerSizeChangedListener);
            this.playerSizeChangedListener();
            this.checkDepedencies = true;
        };

        MinDurationBehaviour = function(params) {
            for (var param in params) {
                this[param] = params[param];
            }
            var that = this;
            this.isChecked = false;
            this.isValid = false;
            this.durationChangedListener = function() {
                if (isNaN(that.p2s.getDuration().valueOf()) || isNaN(that.p2s.getCurrentTime().valueOf()) || that.p2s.getDuration().valueOf() < 0.5) {
                    return;
                } else {
                    switch (that.creation.creation_minduration_type) {
                        case 'second':
                            if (that.p2s.getDuration().valueOf() >= that.creation.creation_minduration) {
                                that.isValid = true;
                            }
                            break;
                        case 'remaining':
                            //console.log(that.p2s.getDuration().valueOf(), "duration");
                            if ((that.p2s.getDuration().valueOf() - that.p2s.getCurrentTime().valueOf()) >= that.creation.creation_minduration) {
                                that.isValid = true;
                            }
                            break;
                    }
                    that.p2s.removeEventListener(adways.resource.events.DURATION_CHANGED, that.durationChangedListener);
                    that.isChecked = true;
//                    console.log("MinDurationBehaviour");
                    that.cb(that);
                }
            };
            //this.p2s.addEventListener(adways.resource.events.DURATION_CHANGED, this.durationChangedListener);
            //this.durationChangedListener();
        };

        MinDurationBehaviour.prototype.reset = function() {
            this.isChecked = false;
            this.isValid = false;
            this.p2s.removeEventListener(adways.resource.events.DURATION_CHANGED, this.durationChangedListener);
        };

        MinDurationBehaviour.prototype.reCheck = function() {
            this.p2s.addEventListener(adways.resource.events.DURATION_CHANGED, this.durationChangedListener);
            this.durationChangedListener();
        };


        PreventinContentBehaviour = function(params) {
            for (var param in params) {
                this[param] = params[param];
            }
            var that = this;
            this.isChecked = false;
            this.isValid = false;
            this.adStateChangedListener = function() {
                // TODO
                if (that.p2s.getAdPlayState().valueOf() == "playing") {
                    that.p2s.removeEventListener(adways.resource.events.AD_PLAY_STATE_CHANGED, that.adStateChangedListener);
                    that.isChecked = true;
                    that.cb(that);
                }
            };
            //this.p2s.addEventListener(adways.resource.events.AD_PLAY_STATE_CHANGED, this.adStateChangedListener);
            //this.adStateChangedListener();
        };

        PreventinContentBehaviour.prototype.reset = function() {
            this.isChecked = false;
            this.isValid = false;
            this.p2s.removeEventListener(adways.resource.events.AD_PLAY_STATE_CHANGED, this.adStateChangedListener);
        };

        PreventinContentBehaviour.prototype.reCheck = function() {
            this.p2s.addEventListener(adways.resource.events.AD_PLAY_STATE_CHANGED, this.adStateChangedListener);
            this.adStateChangedListener();
        };


        UseVisibilityBehaviour = function(params) {

            for (var param in params) {
                this[param] = params[param];
            }
            this.checkDepedencies = true;
            this.isChecked = false;
            this.isValid = false;
            this.tmpVisible = false;
            this.visibleTimer = 0;
            this.visibilityInterval = null;
            this.boxElement = null;
            this.observer = null;
            if (typeof this.delegate.getVideoElement == "function") {
                this.boxElement = this.delegate.getVideoElement();
            } else {
                this.boxElement = this.playerIdentity[1];
            }
            this.tryToObserve();
        };

        UseVisibilityBehaviour.prototype.tryToObserve = function() {
            var that = this;
            try {
                function tryObserve() {
                    var numSteps = 20.0;

                    function createObserver() {
                        var options = {
                            root: null,
                            rootMargin: "0px",
                            threshold: buildThresholdList()
                        };
                        that.observer = new IntersectionObserver(handleIntersect, options);
                        //that.observer.observe(that.boxElement);
                    };

                    function buildThresholdList() {
                        var thresholds = [];
                        for (var i = 1.0; i <= numSteps; i++) {
                            var ratio = i / numSteps;
                            thresholds.push(ratio);
                        }
                        thresholds.push(0);
                        return thresholds;
                    };

                    function handleIntersect(entries, observer) {
                        entries.forEach(function(entry) {
                            var percent = that.creation.creation_visibility_percent;
                            var timer = that.creation.creation_visibility_timer;
                            that.isChecked = true;
                            if ((entry.intersectionRatio + 0.01) >= percent / 100) {
                                if (timer > 0) {
                                    if (!that.isVisible && !that.tmpVisible) {
                                        that.tmpVisible = true;
//                                        console.log("UseVisibilityBehaviour visibilityInterval");
                                        that.visibilityInterval = setInterval(function() {
                                            that.visibleTimer++;
                                            if (that.visibleTimer >= timer) {
                                                that.tmpVisible = false;
                                                that.visibleTimer = 0;
                                                clearInterval(that.visibilityInterval);
                                                that.visibilityInterval = null;
                                                that.observer.unobserve(that.boxElement);
                                                that.isValid = true;
//                            console.log("UseVisibilityBehaviour", that.checkDepedencies);
                                                if (that.checkDepedencies)
                                                    that.cb(that);
                                            }
//                                                that.cb(that);
                                        }, 1000);
                                    }
                                } else {
                                    that.observer.unobserve(that.boxElement);
                                    that.isValid = true;
                                    if (that.checkDepedencies)
                                        that.cb(that);
                                }
                            } else {
                                that.tmpVisible = false;
                                clearInterval(that.visibilityInterval);
                                that.visibilityInterval = null;
                                that.visibleTimer = 0;
                            }
//                            console.log("UseVisibilityBehaviour 2", that.checkDepedencies);
//                            if (that.checkDepedencies)
//                                that.cb(that);
                        });
                    };
                    createObserver();
                }
                if (document.visibilityState == "visible") {
                    tryObserve();
                } else {
                    function handleVisibilityChange() {
                        if (document.visibilityState == "visible") {
                            document.removeEventListener("visibilitychange", handleVisibilityChange);
                            tryObserve();
                        }
                    }
                    document.addEventListener("visibilitychange", handleVisibilityChange);
                }
            } catch (e) {
                this.isValid = true; //pour lancer quand meme si ca bug
                this.isChecked = true;
                this.cb(that);
            }
        };

        UseVisibilityBehaviour.prototype.reset = function() {
            this.isValid = false;
            this.tmpVisible = false;
            this.visibleTimer = 0;
            clearInterval(this.visibilityInterval);
            this.visibilityInterval = null;
            try {
                this.observer.unobserve(this.boxElement);
            } catch (e) {}
        };

        UseVisibilityBehaviour.prototype.reCheck = function() {
            this.checkDepedencies = arguments.length > 0 ? arguments[0] : true;
            var that = this;
            try {
                this.observer.observe(this.boxElement);
            } catch (e) {
                this.isValid = true; //pour lancer quand meme si ca bug
                this.isChecked = true;
                this.cb(that);
            }
            setTimeout(function(){ 
//                console.log("reCheck setTimeout");
                that.checkDepedencies = true; 
            }, (that.creation.creation_visibility_timer) * 1000);
        };
// return type + tag | emplacement ID
var Targetizer = function(win, targTab, domain = '') {
    
//console.log("adwdebug Targetizer",targTab );
    var returnInventories = new Array();
    for (var i = 0; i < targTab.length; i++) {
        // we check first if the domain is ok
        var domainToCheck = win.location.hostname;
        if(domain !== '')
            domainToCheck = domain;
        if (domainToCheck.match(targTab[i].domain) != null) {
            // we sort URLs to check the biggest before
            var inventories = targTab[i]['inventories'];
            for (var l = 0; l < inventories.length; l++) {
                var subPageFound = false;
                var inventory = inventories[l];
                if (inventory.nb_sub_pages && inventory.nb_sub_pages > 0) {
                    inventory.sub_pages.sort(function(a, b) {
                        return b.url.length - a.url.length;
                    });
                    for (var j = 0; j < inventory.sub_pages.length; j++) {
                        var subPageToCheck = win.location.href;
                        if(domain !== '')
                            subPageToCheck = domain;
                        if (subPageToCheck.match(inventory.sub_pages[j].url) != null) {
                            if ((inventory.sub_pages[j].creation_mobileonly && Targetizer.mobileAndTabletcheck() && !inventory.sub_pages[j].creation_desktoponly)
                                    || (inventory.sub_pages[j].creation_desktoponly && !Targetizer.mobileAndTabletcheck() && !inventory.sub_pages[j].creation_mobileonly)
                                    || (!inventory.sub_pages[j].creation_desktoponly && !inventory.sub_pages[j].creation_mobileonly)
                                    || (inventory.sub_pages[j].creation_desktoponly && inventory.sub_pages[j].creation_mobileonly)) {
                                if (inventory.sub_pages[j].creation_type != '' && inventory.sub_pages[j].creation_url != '') {
                                    returnInventories.push(inventory.sub_pages[j]);
                                    subPageFound = true;
                                }
                            }
                        }
                    }
                }
                if(subPageFound)
                    continue;
                if ((inventory.creation_mobileonly && Targetizer.mobileAndTabletcheck() && !inventory.creation_desktoponly)
                        || (inventory.creation_desktoponly && !Targetizer.mobileAndTabletcheck() && !inventory.creation_mobileonly)
                        || (!inventory.creation_desktoponly && !inventory.creation_mobileonly)
                        || (inventory.creation_desktoponly && inventory.creation_mobileonly)) {
                    if (inventory.creation_type != '' && inventory.creation_url != '') {
                        returnInventories.push(inventory);
                    }
                }
            }
        }
    }
    return returnInventories;
};

Targetizer.createFiFrame = function(doc) {
    var fif = doc.createElement("iframe");
    fif.webkitAllowFullscreen = true;
    fif.mozAllowFullscreen = true;
    fif.msAllowFullscreen = true;
    fif.allowFullscreen = true;
    fif.style.margin = 0;
    fif.style.padding = 0;
    fif.style.border = 0;
    fif.style.width = "0";
    fif.style.height = "0";
    if(arguments.length>1) {
        var cb = arguments[1];
        fif.addEventListener("load", function() {
            cb(fif);
        });
    }
    doc.body.appendChild(fif);
    return fif;
};

Targetizer.mobileAndTabletcheck = function() {
    var check = false;
    (function(a) {
        if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4)))
            check = true;
    })(navigator.userAgent || navigator.vendor || window.opera);
    return check;
};        //    if (typeof LocatePlayerAlreadyLaunched == "undefined")
        //        LocatePlayerAlreadyLaunched = false;
        var LocatePlayer = function(doc, config, creation, tracker) {
            this.tracker = tracker;
            this.bridge = new Bridge();
            this.myVastLoaderManager = null;

            if (typeof doc == "undefined")
                this.doc = document;
            else
                this.doc = doc;
            this.debug = 0;
                        this.config = config;
            this.forbiddenPlayers = [];
            this.observers = [];
            this.creation = creation;
            this.playersToCheckAgain = [];
            this.playerToCheckAgainInterval = null;

            if (this.config != null) {
                if (typeof this.config.creation == "object")
                    this.creation = this.config.creation;
                if (typeof this.config.forbiddenPlayers == "object")
                    this.forbiddenPlayers = this.config.forbiddenPlayers;
            }
            this.vastloaders = new Array();
            this.playerAlreadyInteractive = new Array();
            //        this.MyIdentifyPlayer = null;
            //        this.VastLoaderManager = null;
            this.maxInteractivePlayer = 1;
            this.maxInstantiation = 1;
        };

        LocatePlayer.prototype.search = function(customDoc) {
            var doc = this.doc;
            var that = this;
            if (typeof customDoc != "undefined") {
                var doc = customDoc;
            }
            var videos = doc.querySelectorAll('video');
            var that = this;
            [].forEach.call(videos, function(video) {
                //        video.style.border = "5px solid red";
                that.detectPlayer(video);
            });
            var iframes = doc.querySelectorAll('iframe');
            var that = this;
            [].forEach.call(iframes, function(iframe) {
                //        iframe.style.border = "5px solid yellow";

                var hostToCheck = window.location.host.split(".");
                if (hostToCheck.length > 1) {
                    hostToCheck = hostToCheck[hostToCheck.length - 2] + "\." + hostToCheck[hostToCheck.length - 1];
                } else {
                    hostToCheck = hostToCheck[0];
                }
                eval('var re = /' + hostToCheck + '/;');
                //        eval('var re = /^' + window.location.protocol + '\\/\\/' + window.location.host.replace(/\./g, "\\.") + '/;');
                var clientToCheck = iframe.src.split("//");
                if (clientToCheck.length > 1) {
                    clientToCheck = clientToCheck[1].split("/");
                    clientToCheck = clientToCheck[0];
                } else {
                    clientToCheck = hostToCheck[0];
                }
                var tmp = clientToCheck.match(re);
                var forcePass = false;
                try {
                    if (window.location.origin == iframe.contentWindow.location.origin)
                        forcePass = true;
                } catch (e) {}
                if (tmp != null || forcePass) {
                    try {
                        // TODO: check later if needed
                        // cross domain error forcing the domain
                        //                document.domain = hostToCheck;
                        var doc = iframe.contentDocument ? iframe.contentDocument : (iframe.contentWindow ? iframe.contentWindow.document : iframe.document);
                        that.search(doc);
                        forcePass = false;
                    } catch (e) {
                        console.log("publisher.search in iframe cross domain error: ", iframe);
                    }
                } else {
                    that.detectPlayer(iframe);
                }
            });
            var swObjects = doc.querySelectorAll('object');
            var that = this;
            [].forEach.call(swObjects, function(swObject) {
                //        swObject.style.border = "5px solid blue";
                that.detectPlayer(swObject);
            });
        };

        LocatePlayer.prototype.observePlayer = function() {
            var that = this;
            MutationObserver = MutationObserver || WebKitMutationObserver;
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (typeof mutation.target == 'function' ||
                        (typeof mutation.target == 'object' &&
                            typeof mutation.target.tagName != 'undefined' &&
                            (mutation.target.tagName.toLowerCase() == 'iframe' || mutation.target.tagName.toLowerCase() == 'video'))
                    ) {
                        if (mutation.target.tagName.toLowerCase() == 'iframe' && mutation.target.location == window.location) {
                            var doc = mutation.target.contentDocument ? mutation.target.contentDocument : (mutation.target.contentWindow ? mutation.target.contentWindow.document : mutation.target.document);
                            that.search(doc);
                        } else {
                            that.detectPlayer(mutation.target);
                        }
                    }
                    if (mutation.type == "childList" && mutation.addedNodes.length > 0) {
                        for (var i = 0; i < mutation.addedNodes.length; i++) {
                            if (typeof mutation.addedNodes[i] == 'function' ||
                                (typeof mutation.addedNodes[i] == 'object' &&
                                    typeof mutation.addedNodes[i].tagName != 'undefined' &&
                                    (mutation.addedNodes[i].tagName.toLowerCase() == 'iframe' || mutation.addedNodes[i].tagName.toLowerCase() == 'video'))
                            ) {
                                if (mutation.addedNodes[i].tagName.toLowerCase() == 'iframe' && mutation.addedNodes[i].location == window.location) {
                                    var doc = mutation.addedNodes[i].contentDocument ? mutation.addedNodes[i].contentDocument : (mutation.addedNodes[i].contentWindow ? mutation.addedNodes[i].contentWindow.document : mutation.addedNodes[i].document);
                                    that.search(doc);
                                } else {
                                    that.detectPlayer(mutation.addedNodes[i]);
                                }
                                //                        mutation.addedNodes[i].style.border = "5px solid blue";
                                //                        if (mutation.addedNodes[i].tagName.toLowerCase() == 'video') {
                                //                            console.log(mutation.addedNodes[i], "--------------------OBJECT OBSERVER");
                                //                        }
                                //if(node.ownerDocument !== document) {
                            }
                        }
                    }
                });
            });
            // configuration of the observer:
            var configObs = {
                attributes: true,
                childList: true,
                characterData: false,
                subtree: true,
                attributeFilter: ["class", "src"]
            };
            // pass in the target node, as well as the observer options
            this.observers.push(observer);
            observer.observe(document.body, configObs);
            this.playerToCheckAgainInterval = setInterval(function() {
                that.playerToCheckAgainCB();
            }, 5000);
            setTimeout(
                function() {
                    clearInterval(that.playerToCheckAgainInterval);
                }, 60000);
        };

        LocatePlayer.prototype.playerToCheckAgainCB = function() {
            if (this.playersToCheckAgain.length > 0) {
                for (var i = 0; i < this.playersToCheckAgain.length; i++) {
                    this.detectPlayer(this.playersToCheckAgain[i]);
                }
            }
        };

        LocatePlayer.prototype.detectPlayer = function(jsObject) {
            var that = this;
            var MyIdentifyPlayer = new IdentifyPlayer(this, this.forbiddenPlayers, this.playersToCheckAgain);
            var playerIdentity = MyIdentifyPlayer.playerFromJSObject(jsObject);
            if (playerIdentity.length < 1) {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        //if (mutation.attributeName == "class" || mutation.attributeName == "src") {
                        playerIdentity = MyIdentifyPlayer.playerFromJSObject(mutation.target);
                        //}
                        that.createInteractivityOnPlayerDetected(playerIdentity);
                        return;
                    });
                });
                // configuration of the observer:
                var config = {
                    attributes: true,
                    childList: false,
                    characterData: true,
                    subtree: false,
                    attributeFilter: ["class", "src"]
                };
                // pass in the target node, as well as the observer options
                this.observers.push(observer);
                observer.observe(jsObject, config);
            } else {
                this.createInteractivityOnPlayerDetected(playerIdentity);
            }

            if (this.debug == 1 && playerIdentity.length > 0) {
                console.log(playerIdentity[0]);
            }
            return playerIdentity;

        };

        LocatePlayer.prototype.instantiateInteractivity = function(playerIdentity) {
            this.playerIdentity = playerIdentity;
                            if (this.tracker !== null) {
                    var completionTime = (Date.now() - this.tracker.timeInitTrackerMC) / 1000;
                    this.tracker.sendData({
                        event_type: 'state',
                        event_name: 'playerDetected',
                        completion_value: completionTime,
                        completion_ref: 'Fsjjnzg'
                    });
                }
                if (this.bridge.getAdwPBSReceived()) {
                    console.log("getAdwPBSReceived");
                    return;
                }
                var that = this;
                this.adCalls = new Array();
                this.originalAdCalls = new Array();
                var adCall = new Object();
                adCall.url = that.creation.creation_url;
                adCall.type = that.creation.creation_type;
                adCall.capping = that.creation.creation_capping;
                adCall.indice = -1;
                this.adCalls.push(adCall);
                if (typeof that.creation.creation_backfills !== "undefined") {
                    if (that.creation.creation_backfills.length > 0) {
                        for (var i = 0; i < that.creation.creation_backfills.length; i++) {
                            var adCall = new Object();
                            adCall.capping = typeof that.creation.creation_backfills[i]["creation_capping"] !== 'undefined' ? that.creation.creation_backfills[i]["creation_capping"] : 0;
                            adCall.url = that.creation.creation_backfills[i]["creation_url"];
                            adCall.type = that.creation.creation_backfills[i]["creation_type"];
                            adCall.indice = i;
                            this.adCalls.push(adCall);
                        }
                    }
                }
                this.originalAdCalls = Array.prototype.slice.call(this.adCalls);
                var checkBehaviour = new BehaviourChecker({
                    'playerIdentity': playerIdentity,
                    'cb': function() {
                        that.loadCreative.apply(that, Array.prototype.slice.call(arguments));
                    },
                    'repeatCb': function() {
                        that.adCalls = Array.prototype.slice.call(that.originalAdCalls);
                    },
                    'creation': that.creation
                });
                    };

        LocatePlayer.prototype.loadCreative = function() {
            var that = this;
            var delegate = arguments.length > 0 ? arguments[0] : null;
            var s2p = arguments.length > 1 ? arguments[1] : null;
            var p2s = arguments.length > 2 ? arguments[2] : null;
            //        console.log("instantiateInteractivity", that.adCalls);
            if (that.adCalls.length < 1)
                return 0;
            if (that.adCalls[0].type == "vast" || that.adCalls[0].type == "vast_linear") {
                var vastLoaderTmp = (that.config != null && typeof that.config.VASTLoader == "string") ? that.config.VASTLoader : null;
                if (vastLoaderTmp == null) {
                    if (that.adCalls[0].type == "vast")
                        vastLoaderTmp = "ADWLoader";
                    else
                        vastLoaderTmp = "IMAPlugin";
                }
                that.myVastLoaderManager = new VastLoaderManager(that.playerIdentity, vastLoaderTmp,
                    that.creation,
                    that.tracker,
                    delegate,
                    s2p,
                    p2s,
                    function() {
                        that.instantiateInteractivity(that.playerIdentity);
                    },
                    that.adCalls,
                    function() {
                        that.loadCreative(delegate, s2p, p2s);
                    });

            } else if (that.adCalls[0].type == "v2_publication_id") {
                var MyPublicationManager = new PublicationManager(
                    that.playerIdentity,
                    (that.config != null && typeof that.config.studioVersion == "string") ? that.config.studioVersion : "V2",
                    that.creation.creation_url);
            } else if (that.adCalls[0].type == "vpaid") {
                var MyVPAIDManager = new VPAIDManager(
                    that.playerIdentity,
                    that.creation,
                    that.tracker,
                    delegate,
                    s2p,
                    p2s,
                    function() {
                        that.instantiateInteractivity(that.playerIdentity);
                    },
                    that.adCalls,
                    function() {
                        that.loadCreative(delegate, s2p, p2s);
                    });
            }
            return 1;
        };

        LocatePlayer.prototype.buildPlayer = function(playerIdentity) {

            var Experience = function(playerName, options, locatePlayer) {
                this.options = options;
                this.playerName = playerName;
                this.locatePlayer = locatePlayer;
            };
            Experience.prototype.getMediaParams = function() {
                return this.options.mediaParams;
            };
            Experience.prototype.getOptions = function() {
                return this.options.options;
            };
            Experience.prototype.getPlayerParams = function() {
                return this.options.playerParams;
            };
            Experience.prototype.playerCreatedCB = function(playerAPI) {
                //            console.log("MC - playerReady", playerAPI.getIframe());
                this.locatePlayer.playerAlreadyInteractive.push(playerAPI.getIframe());
            };
            Experience.prototype.playerReady = function(playerAPI) {
                //                        console.log("MC - playerReady", playerAPI.getIframe());
                if (this.playerName == 'youtube') {
                    var publisherParamsTMP = null;
                    if (publisherParamsTMP = getPublisherParams()) {
                        if (typeof publisherParamsTMP.youtubeParams !== 'undefined' && typeof publisherParamsTMP.youtubeParams.css !== 'undefined') {
                            var player = playerAPI.getIframe();
                            for (var property1 in publisherParamsTMP.youtubeParams.css) {
                                player.style[property1] = publisherParamsTMP.youtubeParams.css[property1];
                            }
                        }
                    }
                }
                this.locatePlayer.instantiateInteractivity([this.playerName, playerAPI]);
                return 1;
            };
            if (playerIdentity[0] == 'youtube') {
                playerIdentity[1].remove();
                var exp = new Experience(playerIdentity[0], playerIdentity[2].rebuildPlayer, this);
                if (typeof(adways) != "undefined" && typeof(adways.playerHelpers) != "undefined" && typeof(adways.playerHelpers.YouTubePlayerBuilder) != "undefined") {
                    new adways.playerHelpers.YouTubePlayerBuilder(playerIdentity[2].rebuildPlayer.container, exp);
                } else {
                    var playerBuilderScript = document.createElement('script');
                    playerBuilderScript.type = "text/javascript";
                    playerBuilderScript.src = "https://play.adpaths.com/libs/playerHelpers/builders/youtube.js";
                    playerBuilderScript.addEventListener('load', function() {
                        new adways.playerHelpers.YouTubePlayerBuilder(playerIdentity[2].rebuildPlayer.container, exp);
                    });
                    document.body.appendChild(playerBuilderScript);
                }
            } else {
                return -1;
            }
        };

        LocatePlayer.prototype.createInteractivityOnPlayerDetected = function(playerIdentity) {
            if (playerIdentity.length > 0) {
                //                        console.log("MC - createInteractivityOnPlayerDetected 0", playerIdentity[1], this.playerAlreadyInteractive);
                if (playerIdentity.length == 2 || (playerIdentity.length > 2 && typeof playerIdentity[2] == "object")) {
                    if ((!(this.playerAlreadyInteractive.indexOf(playerIdentity[1]) > -1) && this.maxInteractivePlayer == 0) ||
                        (!(this.playerAlreadyInteractive.indexOf(playerIdentity[1]) > -1) && this.playerAlreadyInteractive.length < this.maxInteractivePlayer)) {
                        //            console.log("MC - createInteractivityOnPlayerDetected 1", playerIdentity[1]);
                        if (playerIdentity.length > 2 && playerIdentity[2] !== null && playerIdentity[2]['rebuildPlayer']) {
                            this.buildPlayer(playerIdentity);
                        } else {
                            this.instantiateInteractivity(playerIdentity);
                        }
                        this.playerAlreadyInteractive.push(playerIdentity[1]);
                        if (this.maxInteractivePlayer > 0 && this.playerAlreadyInteractive.length >= this.maxInteractivePlayer) {
                            var that = this;
                            [].forEach.call(this.observers, function(anObserver) {
                                anObserver.disconnect();
                                if (that.debug == 1) {
                                    console.log("disconnect");
                                }
                            });
                        }
                        return 1;
                    }
                }
            }
            return 0;
        };

        LocatePlayer.prototype.checkPlayerIDs = function() {

            var that = this;

                                                                                                        };
var Bridge = function () {
    this.source = null;
    this.origin = null;
    this.adwPBSReceived = false;
    this.intervalChecker = null;
    this.init = false;
    
    var that = this;
    this.postMessageListener = function (e) {
        if (e.origin === "http://www.dailymotion.com" || e.origin === "https://www.dailymotion.com") {
            if (that.source === null) {
                that.source = e.source;
                that.origin = e.origin;
                that.intervalChecker = setInterval(function () {
                    if(that.init) {
//                        console.log("publisher adwpbs");
                        that.source.postMessage("adwpbs", that.origin);
                    }
                }, 1000);
            }
        } else {
            if (typeof e.data == "string" && e.data === "adwpbs") {
//                console.log("postMessageListener Player", e);
                that.adwPBSReceived = true;
            }
        }
    };
    var win = document.defaultView || document.parentWindow;
    win.addEventListener("message", this.postMessageListener, false);
};

Bridge.prototype.getAdwPBSReceived = function () {
    return this.adwPBSReceived;
};

Bridge.prototype.initMessage = function () {
//    console.log("initMessage");
    this.init = true;
};

Bridge.prototype.destroy = function () {
    var win = document.defaultView || document.parentWindow;
    win.removeEventListener("message", this.postMessageListener, false);    
    clearInterval(this.intervalChecker);
    this.intervalChecker = null;
};
        var CustomIMAPlugin = function(playerIdentity, creation, delegate, s2p, p2s) {
            var that = this;
            this.adsManager = null;
            this.creation = creation;
            this.playerIdentity = playerIdentity;
            this.adDisplayContainer;
            this.adsLoader;
            this.adsRequest;
            this.delegateClassName = null;
            this.adContainer;
            this.adPlayed = false;
            this.FiFSkip = null;
            this.FiFLogo = null;
            this.FiFBar = null;
            this.FiFMute = null;
            this.layer = null;
            this.delegate = typeof delegate != "undefined" ? delegate : null;
            this.s2p = typeof s2p != "undefined" ? s2p : null;
            this.p2s = typeof p2s != "undefined" ? p2s : null;

            this.onVolumeChange = function() {
                if (that.adsManager.getVolume() < 0.1) {
                    that.FiFMuteDoc.body.style.backgroundImage = "url(https://assets.adpaths.com/17/2019/11/5dc2f0638438d.png)";
                } else {
                    that.FiFMuteDoc.body.style.backgroundImage = "url(https://assets.adpaths.com/17/2019/11/5dc2f058bbed8.png)";
                }
            }

            this.onAdEvent = function(adEvent) {
                // Retrieve the ad from the event. Some events (e.g. ALL_ADS_COMPLETED)
                // don't have ad object associated.
                var ad = adEvent.getAd();
                // console.log(ad, that.adsManager);
                switch (adEvent.type) {
                    case google.ima.AdEvent.Type.LOADED:
                        if (!ad.isLinear()) {
                            that.s2p.play(true);
                        }
                        break;
                    case google.ima.AdEvent.Type.STARTED:
                        if (that.creation.creation_linear_ui && ad.isLinear() && ad.getApiFramework().toUpperCase() == "VPAID") {
                            that.buildUI(ad.getDuration());
                        }
                        if (that.creation.creation_skipbutton && ad.isLinear() && ad.getApiFramework().toUpperCase() == "VPAID" && ad.getSkipTimeOffset() && ad.getSkipTimeOffset() > 1) {
                            that.buildSkip(ad.getSkipTimeOffset());
                        }
                        break;
                    case google.ima.AdEvent.Type.COMPLETE:
                        if (ad.isLinear()) {
                            clearInterval(that.intervalTimer);
                            clearInterval(that.intervalTimer2);
                        }
                        break;
                    case google.ima.AdEvent.Type.VOLUME_CHANGED:
                    case google.ima.AdEvent.Type.VOLUME_MUTED:
                        if (that.creation.creation_linear_ui && ad.isLinear() && ad.getApiFramework().toUpperCase() == "VPAID") {
                            that.onVolumeChange();
                        }
                        break;
                }
            }

            this.onAdsManagerLoaded = function(adsManagerLoadedEvent) {
                that.adsRenderingSettings = new google.ima.AdsRenderingSettings();
                that.adsRenderingSettings.restoreCustomPlaybackStateOnAdBreakComplete = true;
                that.SCW2HTMLVideoElement = new adways.interactive.SCW2HTMLVideoElement(that.s2p, that.p2s);
                that.adsManager = adsManagerLoadedEvent.getAdsManager(that.SCW2HTMLVideoElement, that.adsRenderingSettings);
                that.p2s.addEventListener(adways.resource.events.PLAYER_SIZE_CHANGED, function() {
                    var playerSize = that.p2s.getPlayerSize().valueOf();
                    that.adsManager.resize(playerSize[0], playerSize[1], google.ima.ViewMode.NORMAL);
                    that.FiFSkip.style.setProperty("top", playerSize[1] - 110 + "px", "important");
                    that.FiFSkip.style.setProperty("right", -playerSize[0] + 20 + "px", "important");
                });

                // Add listeners to the required events.
                that.adsManager.addEventListener(
                    google.ima.AdErrorEvent.Type.AD_ERROR,
                    that.onAdError);
                that.adsManager.addEventListener(
                    google.ima.AdEvent.Type.CONTENT_PAUSE_REQUESTED,
                    that.onContentPauseRequested);
                that.adsManager.addEventListener(
                    google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED,
                    that.onContentResumeRequested);
                that.adsManager.addEventListener(
                    google.ima.AdEvent.Type.ALL_ADS_COMPLETED,
                    that.onAdEvent);

                // Listen to any additional events, if necessary.
                that.adsManager.addEventListener(
                    google.ima.AdEvent.Type.LOADED,
                    that.onAdEvent);
                that.adsManager.addEventListener(
                    google.ima.AdEvent.Type.STARTED,
                    that.onAdEvent);
                that.adsManager.addEventListener(
                    google.ima.AdEvent.Type.COMPLETE,
                    that.onAdEvent);
                that.adsManager.addEventListener(
                    google.ima.AdEvent.Type.VOLUME_MUTED,
                    that.onAdEvent);
                that.adsManager.addEventListener(
                    google.ima.AdEvent.Type.VOLUME_CHANGED,
                    that.onAdEvent);

                that.playAds();
                that.p2s.addEventListener(adways.resource.events.PLAY_STATE_CHANGED, function() {
                    that.playAds();
                });
            }

            this.onAdError = function(adErrorEvent) {
                if (that.adsManager != null)
                    that.adsManager.destroy();
                if (that.delegate != null)
                    that.delegate.destroy();
                if (that.layer)
                    adways.destruct(that.layer);
            }

            this.onContentPauseRequested = function() {
                that.adContainer.style.display = "block";
                that.s2p.pause(true);
            }

            this.onContentResumeRequested = function() {
                that.s2p.play(true);
                that.adContainer.style.display = "none";
                that.adPlayed = true;
            }

            this.IMAScriptCB = function() {
                if (!that.s2p || !that.p2s || !that.delegate) {
                    that.s2p = new adways.interactive.SceneControllerWrapper();
                    that.p2s = new adways.interactive.SceneControllerWrapper();
                    that.delegate = eval("new " + that.delegateClassName + "(that.p2s, that.s2p, that.playerIdentity[1])");
                }
                that.layer = new adways.interactive.Layer(adways.hv.layerIds.TOP);
                that.s2p.layerAdded(that.layer);
                that.adContainer = that.layer.getDomElement();
                var playerSize = that.p2s.getPlayerSize().valueOf();
                // to be sure
                google.ima.settings.setVpaidMode(google.ima.ImaSdkSettings.VpaidMode.ENABLED);
                that.adDisplayContainer = new google.ima.AdDisplayContainer(that.adContainer);
                that.adContainer.style.display = "none";

                that.adsLoader = new google.ima.AdsLoader(that.adDisplayContainer);
                // that.currentVastLoader = that.adsLoader;
                // Listen and respond to ads loaded and error events.
                that.adsLoader.addEventListener(
                    google.ima.AdsManagerLoadedEvent.Type.ADS_MANAGER_LOADED,
                    that.onAdsManagerLoaded,
                    false);
                that.adsLoader.addEventListener(
                    google.ima.AdErrorEvent.Type.AD_ERROR,
                    that.onAdError,
                    false);

                // Request video ads.
                that.adsRequest = new google.ima.AdsRequest();
                that.adsRequest.adTagUrl = that.creation.creation_url;

                // Specify the linear and nonlinear slot sizes. This helps the SDK to
                // select the correct creative if multiple are returned.
                that.adsRequest.linearAdSlotWidth = playerSize[0];
                that.adsRequest.linearAdSlotHeight = playerSize[1];

                that.adsRequest.nonLinearAdSlotWidth = playerSize[0];
                that.adsRequest.nonLinearAdSlotHeight = playerSize[1];

                that.adsLoader.requestAds(that.adsRequest);

            };
            this.CallIMAScript = function() {

                var IMAScript = document.createElement("script");
                IMAScript.type = "text/javascript";
                IMAScript.src = "//imasdk.googleapis.com/js/sdkloader/ima3.js";
                IMAScript.addEventListener("load", function() {
                    that.IMAScriptCB();
                });
                document.getElementsByTagName("head")[0].appendChild(IMAScript);
            };
            this.SCWLibCB = function() {
                if (that.delegate == null) {
                    var playerClassGetURL = "https://d1afeohcmx2qm4.cloudfront.net/player-class?filter-js_constant=" + playerIdentity[0].toUpperCase();
                    var playerClassRequest = new adways.ajax.Request();
                    playerClassRequest.setURL(playerClassGetURL.replace("http://", "//"));
                    playerClassRequest.setMethod("GET");
                    playerClassRequest.addHeader("Accept", "application/json");
                    playerClassRequest.setContentType("application/json");
                    var requestDoneListener = function(evt) {
                        if (playerClassRequest != null && playerClassRequest.getState() === adways.ajax.states.DONE) {
                            playerClassRequest.removeEventListener(adways.ajax.events.STATE_CHANGED, requestDoneListener);
                            var responseText = playerClassRequest.getResponseText();
                            playerClassRequest = null;
                            var responseParsed = null;
                            responseParsed = JSON.parse(responseText);
                            if (responseParsed['_embedded'] && responseParsed['_embedded']['collection'] &&
                                responseParsed['_embedded']['collection'][0]) {

                                var delegateUrl = responseParsed['_embedded']['collection'][0]["delegate_url"];
                                // fix later the path issue "/"
                                var re = new RegExp('//', 'i');
                                if (delegateUrl.match(re) == null)
                                    delegateUrl = "https://play.adpaths.com/" + delegateUrl.replace("/libs/", "libs/");

                                that.delegateClassName = responseParsed['_embedded']['collection'][0]["delegate_classname"];

                                var delegateScript = document.createElement("script");
                                delegateScript.type = "text/javascript";
                                delegateScript.src = delegateUrl;
                                delegateScript.addEventListener("load", that.CallIMAScript);
                                document.getElementsByTagName("head")[0].appendChild(delegateScript);
                            } else {
                                // empty, not cool
                            }
                        }
                    };
                    playerClassRequest.addEventListener(adways.ajax.events.STATE_CHANGED, requestDoneListener);
                    playerClassRequest.load();
                } else {
                    that.CallIMAScript();
                }
            };
            var adwLibScw = document.createElement("script");
            adwLibScw.type = "text/javascript";
            adwLibScw.src = "https://play.adpaths.com/libs/scw/release.min.js";
            adwLibScw.addEventListener("load", that.SCWLibCB);
            document.getElementsByTagName("head")[0].appendChild(adwLibScw);
        };

        CustomIMAPlugin.prototype.buildFiFrame = function(container, cb) {
            var that = this;
            var fiframe = container.ownerDocument.createElement("iframe");
            var fiframeDoc = null;
            fiframe.sandbox = "allow-same-origin allow-scripts allow-popups allow-forms";
            fiframe.style.setProperty("border", "0px", "important");
            fiframe.style.setProperty("overflow", "hidden", "important");
            fiframe.style.setProperty("scrolling", "no", "important");
            fiframe.style.setProperty("position", "absolute", "important");
            fiframe.style.setProperty("top", "0px", "important");
            fiframe.style.setProperty("left", "0px", "important");
            fiframe.style.setProperty("width", "0%", "important");
            fiframe.style.setProperty("height", "0%", "important");
            fiframe.style.setProperty("max-width", "none", "important");
            fiframe.style.setProperty("max-height", "none", "important");
            var loadedFunction = function() {
                if (fiframeDoc) {
                    fiframe.removeEventListener("load", loadedFunction);
                    cb(fiframe, fiframeDoc);
                }
            };
            fiframe.addEventListener("load", loadedFunction);
            container.appendChild(fiframe);
            var a = "<html><head></head><body></body></html>";
            fiframeDoc = fiframe.contentDocument ? fiframe.contentDocument : (fiframe.contentWindow ? fiframe.contentWindow.document : fiframe.document);
            fiframeDoc.open("text/html");
            fiframeDoc.write(a);
            fiframeDoc.close();
            fiframeDoc.body.style.margin = 0;
            fiframeDoc.body.style.border = 0;
            fiframeDoc.body.style.padding = 0;
        };

        CustomIMAPlugin.prototype.buildUI = function(duration) {
            var that = this;

            var buildMuteButton = function(myFiF, myFiFDoc) {
                that.FiFMute = myFiF;
                that.FiFMuteDoc = myFiFDoc;
                var playerSize = that.p2s.getPlayerSize().valueOf();
                myFiF.style.setProperty("top", playerSize[1] - 38 + "px", "important");
                myFiF.style.setProperty("left", "8px", "important");
                myFiF.style.setProperty("width", "22px", "important");
                myFiF.style.setProperty("height", "22px", "important");
                myFiF.style.setProperty("z-index", "808", "important");
                myFiFDoc.body.style.background = "url(https://assets.adpaths.com/17/2019/11/5dc2f058bbed8.png) center center no-repeat transparent";
                that.onVolumeChange();
                myFiFDoc.body.style.backgroundSize = "cover";
                myFiFDoc.body.style.cursor = "pointer";
                myFiFDoc.body.addEventListener("click", function() {
                    if (that.adsManager.getVolume() < 0.1) {
                        that.adsManager.setVolume(1);
                    } else {
                        that.adsManager.setVolume(0);
                    }
                    that.onVolumeChange();
                });
            };
            var buildLogo = function(myFiF, myFiFDoc) {
                that.FiFLogo = myFiF;
                var playerSize = that.p2s.getPlayerSize().valueOf();
                myFiF.style.setProperty("top", playerSize[1] + 2 + "px", "important");
                myFiF.style.setProperty("left", "auto", "important");
                myFiF.style.setProperty("right", -playerSize[0] + 2 + "px", "important");
                myFiF.style.setProperty("width", "60px", "important");
                myFiF.style.setProperty("height", "17px", "important");
                myFiF.style.setProperty("z-index", "808", "important");
                myFiFDoc.body.style.background = "url(https://assets.adpaths.com/17/2019/11/5dc2ec6edbd65.png) center center no-repeat transparent";
                myFiFDoc.body.style.backgroundSize = "cover";
            };

            var buildBar = function(myFiF, myFiFDoc) {
                that.FiFBar = myFiF;
                var playerSize = that.p2s.getPlayerSize().valueOf();
                myFiF.style.setProperty("top", playerSize[1] - 3 + "px", "important");
                myFiF.style.setProperty("width", playerSize[0] + "px", "important");
                myFiF.style.setProperty("height", "3px", "important");
                myFiF.style.setProperty("z-index", "808", "important");

                myFiFDoc.body.style.backgroundColor = "#32539d";
                myFiFDoc.body.style.width = "100%";
                myFiFDoc.body.style.height = "100%";

                var progressBar = document.createElement("div");
                progressBar.style.zIndex = 1;
                progressBar.style.background = "rgba(255,255,255,0.4)";
                progressBar.style.width = "0";
                progressBar.style.transition = "width 0.3s linear";
                progressBar.style.height = "100%";
                progressBar.style.position = "absolute";
                progressBar.style.top = "0px";
                progressBar.style.left = "0px";

                myFiFDoc.body.appendChild(progressBar);
                that.intervalTimer2 = setInterval(
                    function() {
                        progressBar.style.width = 100 - that.adsManager.getRemainingTime() / duration * 100 + "%";
                    }, 300);
            };
            this.buildFiFrame(this.adContainer, buildBar);
            this.buildFiFrame(this.adContainer, buildLogo);
            this.buildFiFrame(this.adContainer, buildMuteButton);
        };

        CustomIMAPlugin.prototype.buildSkip = function(skipOffset) {
            var that = this;

            var buildHTML = function(myFiF, myFiFDoc) {
                this.FiFSkip = myFiF;
                var playerSize = that.p2s.getPlayerSize().valueOf();
                myFiF.style.setProperty("top", playerSize[1] - 90 + "px", "important");
                myFiF.style.setProperty("right", -playerSize[0] + 10 + "px", "important");
                myFiF.style.setProperty("left", "auto", "important");
                myFiF.style.setProperty("width", "110px", "important");
                myFiF.style.setProperty("height", "34px", "important");
                myFiF.style.setProperty("z-index", "999", "important");

                var skipButton = document.createElement("div");
                skipButton.className = "ima-adw-skip-button";
                skipButton.style.zIndex = 10;
                skipButton.style.background = "rgba(0,0,0,0.8)";
                skipButton.style.color = "#e6e6e6";
                skipButton.style.fontSize = "11px";
                skipButton.style.width = "calc(100% - 12px)";
                skipButton.style.fontFamily = "arial, sans-serif";
                skipButton.style.fontWeight = "normal";
                skipButton.style.position = "absolute";
                skipButton.style.textAlign = "center";
                skipButton.style.padding = "6px";

                skipButton.style.top = "0px";
                skipButton.style.right = "0px";

                skipButtonText = document.createElement("span");
                skipButtonText.style.verticalAlign = "middle";
                skipButtonText.style.display = "inline-block";
                skipButtonText.style.width = "100%";
                skipButtonText.style.lineHeight = "21px";

                skipButton.appendChild(skipButtonText);

                skipButtonText.innerHTML = "Ignorer dans " + skipOffset--;
                myFiFDoc.body.appendChild(skipButton);
                that.intervalTimer = setInterval(
                    function() {
                        //remainingTime = that.adsManager.getRemainingTime();
                        //You can skip this ad in 
                        if (skipOffset >= 1)
                            skipButtonText.innerHTML = "Ignorer dans " + skipOffset--;
                        else {
                            // clearInterval(that.intervalTimer);
                            myFiF.style.setProperty("width", "90px", "important");
                            skipButton.addEventListener("click", function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                that.adsManager.skip();
                            });
                            skipButtonText.innerHTML = '<span style="vertical-align: middle;font-size:14px;color:rgb(255, 255, 255);cursor: pointer;">Ignorer</span><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABGdBTUEAALGPC/xhBQAAAKBJREFUOBFjYBgFsBD4////TCBmg/Hx0UB1FUD8CYi7cKoDSoLAMSCWxKkIKgFU8xSkGAh+4VQLkQeTz4CkJU6FQAmg/CuYepzqYAqg9E8gnYZLMVCOZANh5mMNV0oMBBmMEa6UGggyFCVckQ1kwhUuVBcHOQMHoKqXqRYpVE02KBGALWyBQUN0OsQILxwGEpX1sIYXDgNhhUM3NvlhLgYAYuCVi7Jf+AUAAAAASUVORK5CYII=" style="vertical-align:middle;display:inline;cursor:pointer;width:15px;margin-left:5.24px;">'
                        }
                    },
                    1000);
            };
            this.buildFiFrame(this.adContainer, buildHTML);
        };

        CustomIMAPlugin.prototype.playAds = function() {
            if (this.adPlayed) {
                this.onAdError();
            } else {
                switch (this.p2s.getPlayState().valueOf()) {
                    case adways.resource.playStates.PLAYING:
                        this.adDisplayContainer.initialize();
                        try {
                            // Initialize the ads manager. Ad rules playlist will start at this time.
                            var playerSize = this.p2s.getPlayerSize().valueOf();
                            this.adsManager.init(playerSize[0], playerSize[1], google.ima.ViewMode.NORMAL);
                            // Call play to start showing the ad. Single video and overlay ads will
                            // start at this time; the call will be ignored for ad rules.
                            this.adsManager.start();

                        } catch (adError) {
                            // An error may be thrown if there was a problem with the VAST response.
                            //console.log(adError);
                        }
                        break;
                }
            }
        };

        var VastLoaderManager = function(playerIdentity, currentVastLoaderName, creation, tracker) {
            this.playerIdentity = playerIdentity;
            this.creation = creation;
            this.currentVastLoaderName = typeof currentVastLoaderName == "undefined" ? "IMAPlugin" : currentVastLoaderName;
            this.currentVastLoader = null;
            this.tracker = tracker;
            this.delegate = arguments.length > 4 ? arguments[4] : null;
            this.s2p = arguments.length > 5 ? arguments[5] : null;
            this.p2s = arguments.length > 6 ? arguments[6] : null;
            this.cbFunction = arguments.length > 7 ? arguments[7] : null;
            this.addCalls = arguments.length > 8 ? arguments[8] : new Array();
            this.backfillFunction = arguments.length > 9 ? arguments[9] : null;

            this.createADWVastLoader = function(playerIdentity, creation, tracker) {
                var vastloader;
                this.tracker = tracker;
                var delegate = arguments.length > 3 ? arguments[3] : null;
                var s2p = arguments.length > 4 ? arguments[4] : null;
                var p2s = arguments.length > 5 ? arguments[5] : null;
                var cbFunction = arguments.length > 6 ? arguments[6] : null;
                var addCalls = arguments.length > 7 ? arguments[7] : new Array();
                var backfillFunction = arguments.length > 8 ? arguments[8] : null;

                var that = this;
                var adwLibCB = function() {
                    vastloader = new adways.iab.vast.Loader();
                    that.currentVastLoader = vastloader;
                    var videoslot = playerIdentity[1];
                    vastloader.setPlayerClass(playerIdentity[0]);
                    vastloader.setVideoSlot(videoslot);
                    var slot = videoslot;
                    if (s2p !== null) {
                        var layer = new adways.interactive.Layer(adways.hv.layerIds.HOTSPOT);
                        s2p.layerAdded(layer);
                        var adContainer = layer.getDomElement();
                        slot = adContainer;
                    } else if (typeof(videoslot.getContainer) == "function" && videoslot.getContainer() !== null) {
                        slot = videoslot.getContainer();
                    }
                    if (typeof videoslot.adwSlot !== "undefined" && videoslot.adwSlot !== null && videoslot.adwSlot !== videoslot) {
                        slot = videoslot.adwSlot;
                    }
                    /** hack pour appnexus wrapper **/
                    if (typeof slot.style == "undefined") {
                        slot.style = new Object();
                        slot.style.margin = 0;
                        slot.style.padding = 0;
                    }
                    if (typeof slot.ownerDocument == "undefined") {
                        slot.ownerDocument = document;
                    }
                    if (typeof slot.appendChild == "undefined") {
                        slot.appendChild = function(url) {
                            document.getElementsByTagName("head")[0].appendChild(url);
                        };
                    }
                    if (typeof slot.addEventListener == "undefined") {
                        slot.addEventListener = function() {};
                    }
                    if (typeof slot.removeEventListener == "undefined") {
                        slot.removeEventListener = function() {};
                    }
                    if (typeof videoslot.addEventListener == "undefined") {
                        videoslot.addEventListener = function() {};
                    }
                    if (typeof videoslot.removeEventListener == "undefined") {
                        videoslot.removeEventListener = function() {};
                    }
                    /** finhack pour appnexus wrapper **/
                    vastloader.setSlot(slot);
                    vastloader.setRendererSize(800, 600);
                    if (delegate !== null)
                        vastloader.setDelegate(delegate);
                    if (s2p !== null)
                        vastloader.setS2P(s2p);
                    if (p2s !== null)
                        vastloader.setP2S(p2s);
                    try {
                        if (window.location.hostname != '') {
                            vastloader.setDomain(window.location.hostname);
                        }
                    } catch (e) {
                        console.log('failed to get domain');
                    }
                    if (typeof videoslot.x_domain !== "undefined" && videoslot.x_domain !== "") {
                        vastloader.setDomain(videoslot.x_domain);
                    } 
                    var currentFallback = 0;
                    vastloader.addEventListener(adways.iab.vast.loaderEvents.VAST_FILE_STATE_CHANGED, function(e) {
                        switch (vastloader.getVASTFileState().valueOf()) {
                            case adways.iab.vast.vastFileStates.EMPTY:
                            case adways.iab.vast.vastFileStates.ERROR:
                                //that.tracker.sendData({event_type: 'state', event_name: 'adCallEmpty'});
                                if (addCalls && addCalls.length > 0) {
                                    if (s2p !== null) {
                                        var layers = s2p.layersToArray();
                                        while (layers.length > 0) {
                                            var layer = layers.pop();
                                            s2p.layerRemoved(layer);
                                        }
                                    }
                                    backfillFunction();
                                } else if (creation.creation_fallbacks && creation.creation_fallbacks.length > 0 && currentFallback < creation.creation_fallbacks.length) {
                                    vastloader.requestVAST(creation.creation_fallbacks[currentFallback]);
                                    that.tracker.sendData({
                                        event_type: 'state',
                                        event_name: 'fallback' + currentFallback
                                    });
                                    currentFallback++;
                                }
                                break;
                        }
                    });
                    vastloader.addEventListener(adways.iab.vast.VPAIDWrapperEvents.AD_ERROR, function(e) {
                        //                                    console.log("AD_ERROR fallback", e.getData());
                        if (/*e.getData() === '901' && */addCalls && addCalls.length > 0) {
                            if (s2p !== null) {
                                var layers = s2p.layersToArray();
                                while (layers.length > 0) {
                                    var layer = layers.pop();
                                    s2p.layerRemoved(layer);
                                }
                            }
                            backfillFunction();
                        } else if (/*e.getData() === '901' && */creation.creation_fallbacks && creation.creation_fallbacks.length > 0 && currentFallback < creation.creation_fallbacks.length) {
                            vastloader.requestVAST(creation.creation_fallbacks[currentFallback]);
                            that.tracker.sendData({
                                event_type: 'state',
                                event_name: 'fallback' + currentFallback
                            });
                            currentFallback++;
                        }
                    });
                    vastloader.addEventListener(adways.iab.vast.VPAIDWrapperEvents.AD_IMPRESSION, function() {
                        if (typeof creation.customCallbacks !== 'undefined' && typeof creation.customCallbacks.dispatchAdImpression === 'function') {
                            creation.customCallbacks.dispatchAdImpression();
                        }
                    });
                    vastloader.addEventListener(adways.iab.vast.VPAIDWrapperEvents.AD_CLICK_THRU, function() {
                        if (typeof creation.customCallbacks !== 'undefined' && typeof creation.customCallbacks.dispatchAdClickThru === 'function') {
                            creation.customCallbacks.dispatchAdClickThru();
                        }
                    });
                    vastloader.addEventListener(adways.iab.vast.VPAIDWrapperEvents.AD_VIDEO_START, function() {
                        if (typeof creation.customCallbacks !== 'undefined' && typeof creation.customCallbacks.dispatchAdVideoStart === 'function') {
                            creation.customCallbacks.dispatchAdVideoStart();
                        }
                    });
                    vastloader.addEventListener(adways.iab.vast.VPAIDWrapperEvents.AD_VIDEO_FIRST_QUARTILE, function() {
                        if (typeof creation.customCallbacks !== 'undefined' && typeof creation.customCallbacks.dispatchAdVideoFirstQuartile === 'function') {
                            creation.customCallbacks.dispatchAdVideoFirstQuartile();
                        }
                    });
                    vastloader.addEventListener(adways.iab.vast.VPAIDWrapperEvents.AD_VIDEO_MIDPOINT, function() {
                        if (typeof creation.customCallbacks !== 'undefined' && typeof creation.customCallbacks.dispatchAdVideoMidpoint === 'function') {
                            creation.customCallbacks.dispatchAdVideoMidpoint();
                        }
                    });
                    vastloader.addEventListener(adways.iab.vast.VPAIDWrapperEvents.AD_VIDEO_THIRD_QUARTILE, function() {
                        if (typeof creation.customCallbacks !== 'undefined' && typeof creation.customCallbacks.dispatchAdVideoThirdQuartile === 'function') {
                            creation.customCallbacks.dispatchAdVideoThirdQuartile();
                        }
                    });
                    vastloader.addEventListener(adways.iab.vast.VPAIDWrapperEvents.AD_VIDEO_COMPLETE, function() {
                        if (typeof creation.customCallbacks !== 'undefined' && typeof creation.customCallbacks.dispatchAdVideoComplete === 'function') {
                            creation.customCallbacks.dispatchAdVideoComplete();
                        }
                    });
                    vastloader.addEventListener(adways.iab.vast.VPAIDWrapperEvents.AD_SKIPPED, function() {
                        if (typeof creation.customCallbacks !== 'undefined' && typeof creation.customCallbacks.dispatchAdSkipped === 'function') {
                            creation.customCallbacks.dispatchAdSkipped();
                        }
                    });
                    // Rajouter les metas des sites
                    // vastloader.requestVAST(creation.creation_url);
                    var findKeywords = function(strToCheck) {
                        var returnKeyword = '';
                        var strToCheck = strToCheck.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                        for (var id in adwKeywords) {
                            var keywordsCollection = adwKeywords[id];
                            var keywordsList = keywordsCollection.associated_keywords.split(',');
                            for (var j = 0; j < keywordsList.length; j++) {
                                var keyword = keywordsList[j].trim();
                                if (keyword !== '') {
                                    var re = new RegExp(keyword.normalize("NFD").replace(/[\u0300-\u036f]/g, ""), 'i');
                                    if (strToCheck.match(re)) {
                                        if (returnKeyword !== '') {
                                            returnKeyword += ',' + keywordsCollection.keyword;
                                        } else {
                                            returnKeyword = keywordsCollection.keyword;
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                        return returnKeyword;
                    };

                    var keywordfound = '';
                    if (typeof adwKeywords !== 'undefined' && adwKeywords !== null) {
                        try {
                            keywordfound = findKeywords(window.document.title);
                        } catch (e) {}
                        /**Digiteka**/
                        try {
                            if (location.hostname.match('ultimedia'))
                                keywordfound = findKeywords(window.parent.dtkPlayer.vinfos.video.title);
                        } catch (e) {}
                        /**fin Digiteka**/
                        /**Pepsia**/
                        try {
                            if (window.document.title.match('Pepsia'))
                                keywordfound = findKeywords(document.getElementById('player_title').innerText);
                        } catch (e) {}
                        /**fin Pepsia**/
                    }

                    if (addCalls.length > 0 && addCalls[0]['indice'] > -1) {
                        //                        console.log("adwdebug vast fallback ", addCalls.length, addCalls[0]['indice'], addCalls);
                        that.tracker.sendData({
                            event_type: 'state',
                            event_name: 'fallback' + addCalls[0]['indice']
                        });
                    } else {
                        that.tracker.sendData({
                            event_type: 'state',
                            event_name: 'adCall'
                        });
                        //                        console.log("adwdebug vast addCall ", addCalls);
                    }
                    var creationObj = addCalls.shift();
                    //                    console.log("keywordfound", keywordfound);
                    function getParameterByName(name, url) {
                        if (!url) url = window.location.href;
                        name = name.replace(/[\[\]]/g, '\\$&');
                        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                            results = regex.exec(url);
                        if (!results) return null;
                        if (!results[2]) return '';
                        return decodeURIComponent(results[2].replace(/\+/g, ' '));
                    }
                    var makeCall = function() {
                        var vastURL;
                        var adwLivePreview = getParameterByName('adwLivePreview');
                        if (adwLivePreview && adwLivePreview !== null && adwLivePreview != '') {
                            vastURL = adwLivePreview;
                        } else {
                            vastURL = creationObj.url;
                            if (keywordfound !== '')
                                vastURL = creationObj.url + '&kw_adways_keywords=' + keywordfound;
                            else
                                vastURL = creationObj.url;
                            if (typeof videoslot.iab !== "undefined" && videoslot.iab !== "") {
                                vastURL += '&iab=' + videoslot.iab + '&kw_adways_iab=' + videoslot.iab;
                            }
                            if (typeof videoslot.options !== "undefined" && videoslot.options !== null && 
                                typeof videoslot.options.enableSendDomain !== "undefined" && videoslot.options.enableSendDomain) {
                                var kw_adways_domain = window.location.hostname;
                                if (typeof videoslot.options.sendDomainFromPartners !== "undefined" && videoslot.options.sendDomainFromPartners
                                    && typeof videoslot.x_domain !== "undefined" && videoslot.x_domain !== "") {
                                    kw_adways_domain = videoslot.x_domain;
                                }
                                vastURL += '&kw_adways_domain=' + encodeURIComponent(kw_adways_domain);                                
                            }
                            if (p2s!==null) {
                                var playerSize = p2s.getPlayerSize().valueOf();
                                var width = playerSize[0];
                                var height = playerSize[1];
                                if(width !== null && !isNaN(width) && width > 0 && height !== null && !isNaN(height) && height > 0) {
                                    var ratio = "169";
                                    var actualRatio = Math.ceil(width/height*100)*1.0/100;
                                    if(actualRatio>1) {
                                        if((actualRatio) < (16/9) && (actualRatio) >= (4/3) ) {
                                            ratio = "43";
                                        } else if((actualRatio) < (4/3) && (actualRatio) >= (1) ) {
                                            ratio = "11";
                                        }
                                    } else if (width === height) {
                                        ratio= "11";
                                    } else {
                                        actualRatio = Math.ceil(height/width*100)*1.0/100;
                                        ratio = "916";
                                        if((actualRatio) < (16/9) && (actualRatio) >= (4/3) ) {
                                            ratio = "34";
                                        } else if((actualRatio) < (4/3) && (actualRatio) >= (1) ) {
                                            ratio = "11";
                                        }                                        
                                    }
                                    vastURL += '&iab=' + videoslot.iab + '&kw_adways_ratio=' + ratio;
                                }
                            }
                        }
                        vastloader.requestVAST(vastURL);
                        if (p2s !== null) {
                            var urChangedCB = function() {
                                p2s.removeEventListener(adways.resource.events.URL_CHANGED, urChangedCB, this);
                                console.log("URL_CHANGED");
                                vastloader.clearAll();
                                if (cbFunction !== null)
                                    cbFunction();
                            };
                            p2s.addEventListener(adways.resource.events.URL_CHANGED, urChangedCB, this);
                        }
                    };

                    var cappingResourceURL = 'https://assets.adpaths.com/capping/capping.html';
                    var domainCapping = 'https://assets.adpaths.com';
                    var prepareCall = function() {
                        if (creationObj.capping > 0) {

                            var ifrm = document.createElement('iframe');

                            function sendMessage() {
                                var message = new Object();
                                message.adwCapping = new Object();
                                message.adwCapping.publicationId = creationObj.url;
                                ifrm.contentWindow.postMessage(message, domainCapping);
                            }

                            function receiveMessage(event) {
                                if (typeof event.data === 'object' && typeof event.data.adwCapping === 'object' &&
                                    typeof event.data.adwCapping.publicationId === 'string' &&
                                    typeof event.data.adwCapping.count === 'number') {
                                    //                                console.log("adwdebug publisher count", event.data.adwCapping.count, ifrm);
                                    window.removeEventListener("message", receiveMessage, false);
                                    if (ifrm.parentNode)
                                        ifrm.parentNode.removeChild(ifrm);
                                    if (event.data.adwCapping.count < creationObj.capping) {
                                        makeCall();
                                    } else {
                                        if (addCalls && addCalls.length > 0) {
                                            if (s2p !== null) {
                                                var layers = s2p.layersToArray();
                                                while (layers.length > 0) {
                                                    var layer = layers.pop();
                                                    s2p.layerRemoved(layer);
                                                }
                                            }
                                            backfillFunction();
                                        } else if (creation.creation_fallbacks && creation.creation_fallbacks.length > 0 && currentFallback < creation.creation_fallbacks.length) {
                                            vastloader.requestVAST(creation.creation_fallbacks[currentFallback]);
                                            that.tracker.sendData({
                                                event_type: 'state',
                                                event_name: 'fallback' + currentFallback
                                            });
                                            currentFallback++;
                                        }
                                    }
                                }
                            }
                            window.addEventListener("message", receiveMessage, false);
                            ifrm.setAttribute("src", cappingResourceURL);
                            ifrm.style.display = "none";
                            ifrm.style.width = "0px";
                            ifrm.style.height = "0px";
                            ifrm.addEventListener("load", sendMessage, false);
                            //                        console.log("adwdebug publisher appendChild", ifrm);
                            document.body.appendChild(ifrm);
                        } else {
                            makeCall();
                        }
                    }

                    /************************************************************/
                    /*** Début Vérification cookie live preview *****************/
                    /************************************************************/
                    var ifrm = document.createElement('iframe');

                    function sendLivePreviewMessage() {
                        var message = new Object();
                        message.adwLivePreview = new Object();
                        message.adwLivePreview.setCookie = false;
                        ifrm.contentWindow.postMessage(message, domainCapping);
                    }

                    function receiveLivePreviewMessage(event) {
                        if (typeof event.data === 'object' && typeof event.data.cookieExists === 'boolean') {
                           // console.log("receiveLivePreviewMessage", event.data.cookieExists, event.data.adwLivePreviewURL);
                            window.removeEventListener("message", receiveLivePreviewMessage, false);
                            var livePreviewUrl = event.data.adwCapping;
                            if (ifrm.parentNode)
                                ifrm.parentNode.removeChild(ifrm);

                            if(!event.data.cookieExists || !event.data.adwLivePreviewURL || event.data.adwLivePreviewURL === '') {
                                prepareCall()
                            }else{  // utilisation du vast en cookie
                                var cookieVastURL = event.data.adwLivePreviewURL;
                                vastloader.requestVAST(decodeURIComponent(cookieVastURL));
                            }
                        }else{ // gestion d'erreur
                            prepareCall();
                        }
                    }
                    window.addEventListener("message", receiveLivePreviewMessage, false);
                    ifrm.setAttribute("src", cappingResourceURL);
                    ifrm.style.display = "none";
                    ifrm.style.width = "0px";
                    ifrm.style.height = "0px";
                    ifrm.addEventListener("load", sendLivePreviewMessage, false);
                    //                        console.log("adwdebug publisher appendChild", ifrm);
                    document.body.appendChild(ifrm);

                    /************************************************************/
                    /*** Fin Vérification cookie live preview *******************/
                    /************************************************************/

                    return vastloader;
                };

                if (typeof adways == "undefined" ||
                    typeof adways.iab == "undefined" ||
                    typeof adways.iab.vast == "undefined" ||
                    typeof adways.iab.vast.Loader == "undefined") {
                    var adwLib = document.createElement("script");
                    adwLib.type = "text/javascript";
                    adwLib.src = "https://play.adpaths.com/libs/iAb/vast/loader.js";
                    adwLib.addEventListener("load", function() {
                        adwLibCB();
                    });
                    document.getElementsByTagName("head")[0].appendChild(adwLib);
                } else {
                    adwLibCB();
                }
            };
            this.initVastLoader();
        };

        VastLoaderManager.prototype.initVastLoader = function() {
            if (this.currentVastLoader == null) {
                switch (this.currentVastLoaderName) {
                    case "IMAPlugin":
                        this.currentVastLoader = new CustomIMAPlugin(this.playerIdentity, this.creation, this.delegate, this.s2p, this.p2s);
                        break;
                    case "ADWLoader":
                        this.createADWVastLoader(this.playerIdentity, this.creation, this.tracker, this.delegate, this.s2p, this.p2s, this.cbFunction, this.addCalls, this.backfillFunction);
                        break;
                }
                return 1;
            }
            return 0;
        };

        VastLoaderManager.prototype.clearAll = function() {
            if (this.currentVastLoader !== null) {
                switch (this.currentVastLoaderName) {
                    case "IMAPlugin":
                        //                    this.currentVastLoader.clearAll();
                        break;
                    case "ADWLoader":
                        this.currentVastLoader.clearAll();
                        break;
                }
                return 1;
            }
            return 0;
        };

var VPAIDManager = function (playerIdentity, creation, tracker) {
//    this.playerIdentity = playerIdentity;
    this.creation = creation;
    this.tracker = tracker;
    this.adwConfig = new Object();
    this.adwConfig.delegate = arguments.length > 3 ? arguments[3] : null;
    this.adwConfig.s2p = arguments.length > 4 ? arguments[4] : null;
    this.adwConfig.p2s = arguments.length > 5 ? arguments[5] : null;
    this.adwConfig.cbFunction = arguments.length > 6 ? arguments[6] : null;
    this.adwConfig.addCalls = arguments.length > 7 ? arguments[7] : new Array();
    this.adwConfig.backfillFunction = arguments.length > 8 ? arguments[8] : null;
    this.adwConfig.videoslot = playerIdentity[1];
    this.adwConfig.playerClass = playerIdentity[0];
    this.adwConfig.slot = this.adwConfig.videoslot;
    if (typeof (this.adwConfig.videoslot.getContainer) == "function" && this.adwConfig.videoslot.getContainer() !== null) {
        this.adwConfig.slot = this.adwConfig.videoslot.getContainer();
    }
    if (typeof this.adwConfig.videoslot.adwSlot !== "undefined" && this.adwConfig.videoslot.adwSlot !== null) {
        this.adwConfig.slot = this.adwConfig.videoslot.adwSlot;
    }
    this.iframes = new Array();
    this.currentFallback = 0;

    var that = this;

    this._loadVPAIDAd = function () {
        var that = this;
        var adwaysTargetId = 'adw-vpaid-' + new Date().getTime();
        var creationObj = this.adwConfig.addCalls.shift();  
        var appNexusId = creationObj.url;  
//        var appNexusId = 16985771;  
        var w = window;
//create empty apntag object if it doesn't exist
        var apntag = w.apntag || {};
//        apntag.debug = true;        
//create a queue on the apntag object       
        apntag.anq = apntag.anq || [];
        apntag.anq.push(function () {
            apntag.onEvent('adNoBid', adwaysTargetId, function(adError, adObj){
//                console.log('adNoBid');
//                console.log(adObj);
//                console.log(adError);
                w.adwVPAIDReadyCb = null;
                if (that.adwConfig.addCalls && that.adwConfig.addCalls.length > 0) {
                    that.adwConfig.backfillFunction();
                } 
            });
//            apntag.onEvent('adAvailable', adwaysTargetId, function(adObj){
//                console.log('adAvailable');
//                console.log(adObj);
//                if(adObj.buyerMemberId === 229) {
//                    w.adwVPAIDReadyCb = null;
//                    if (that.adwConfig.addCalls && that.adwConfig.addCalls.length > 0) {
//                        that.adwConfig.backfillFunction();
//                    } 
//                }
//            });
        }); 
        w.adwVPAIDReadyCb = function (window) {
//            console.log('adwdebug adwVPAIDReadyCb');
            var fn = window['getVPAIDAd'];
            if (fn && typeof fn === 'function') {
                var VPAIDCreative = fn();
                VPAIDCreative.subscribe(function () {
                    if (typeof that.creation.customCallbacks !== 'undefined' && typeof that.creation.customCallbacks.dispatchAdImpression === 'function') {
                        that.creation.customCallbacks.dispatchAdImpression();
                    }
//                console.log("AdImpression");
                }, "AdImpression", this);
                VPAIDCreative.subscribe(function () {
                    if (typeof that.creation.customCallbacks !== 'undefined' && typeof that.creation.customCallbacks.dispatchAdClickThru === 'function') {
                        that.creation.customCallbacks.dispatchAdClickThru();
                    }
//                console.log("AdClickThru");
                }, "AdClickThru", this);
                VPAIDCreative.subscribe(function () {
                    if (typeof that.creation.customCallbacks !== 'undefined' && typeof that.creation.customCallbacks.dispatchAdVideoStart === 'function') {
                        that.creation.customCallbacks.dispatchAdVideoStart();
                    }
                }, "AdVideoStart", this);
                VPAIDCreative.subscribe(function () {
                    if (typeof that.creation.customCallbacks !== 'undefined' && typeof that.creation.customCallbacks.dispatchAdVideoFirstQuartile === 'function') {
                        that.creation.customCallbacks.dispatchAdVideoFirstQuartile();
                    }
                }, "AdVideoFirstQuartile", this);
                VPAIDCreative.subscribe(function () {
                    if (typeof that.creation.customCallbacks !== 'undefined' && typeof that.creation.customCallbacks.dispatchAdVideoMidpoint === 'function') {
                        that.creation.customCallbacks.dispatchAdVideoMidpoint();
                    }
                }, "AdVideoMidpoint", this);
                VPAIDCreative.subscribe(function () {
                    if (typeof that.creation.customCallbacks !== 'undefined' && typeof that.creation.customCallbacks.dispatchAdVideoThirdQuartile === 'function') {
                        that.creation.customCallbacks.dispatchAdVideoThirdQuartile();
                    }
                }, "AdVideoThirdQuartile", this);
                VPAIDCreative.subscribe(function () {
                    if (typeof that.creation.customCallbacks !== 'undefined' && typeof that.creation.customCallbacks.dispatchAdVideoComplete === 'function') {
                        that.creation.customCallbacks.dispatchAdVideoComplete();
                    }
                }, "AdVideoComplete", this);
                VPAIDCreative.subscribe(function () {
                    if (typeof that.creation.customCallbacks !== 'undefined' && typeof that.creation.customCallbacks.dispatchAdSkipped === 'function') {
                        that.creation.customCallbacks.dispatchAdSkipped();
                    }
                }, "AdSkipped", this);
                var creativeData = {};
                var environmentVars = new Object();
                if (that.adwConfig.videoslot !== null)
                    environmentVars["videoSlot"] = that.adwConfig.videoslot;
                if (that.adwConfig.playerClass !== null)
                    environmentVars["playerClass"] = that.adwConfig.playerClass;
                if (that.adwConfig.slot !== null)
                    environmentVars["slot"] = that.adwConfig.slot;
                if (that.adwConfig.delegate !== null) {
                    environmentVars["delegate"] = that.adwConfig.delegate;
                    if (that.adwConfig.slot !== null) {
                        environmentVars["slot"]["delegate"] = environmentVars["delegate"];
                    }
                }
                if (that.adwConfig.p2s !== null) {
                    environmentVars["p2s"] = that.adwConfig.p2s;
                    if (that.adwConfig.slot !== null) {
                        environmentVars["slot"]["p2s"] = environmentVars["p2s"];
                    }
                }
                if (that.adwConfig.s2p !== null) {
                    environmentVars["s2p"] = that.adwConfig.s2p;
                    if (that.adwConfig.slot !== null) {
                        environmentVars["slot"]["s2p"] = environmentVars["s2p"];
                    }
                }
                VPAIDCreative.initAd(16, 9, "normal", 3600, creativeData, environmentVars);
            }
        };

//load ast.js - async
        (function (w) {
            w.apntag = apntag;
//            console.log("adwdebug load ast.js - async");
            var d = w.document, e = d.createElement('script'), p = d.getElementsByTagName('head')[0];
            e.type = 'text/javascript';
            e.async = true;
            e.src = '//acdn.adnxs.com/ast/ast.js';
            p.insertBefore(e, p.firstChild);
        })(w);

//push commands to loading queue, to allow for async loading
        apntag.anq.push(function () {
        //            console.log("adwdebug push");
            //set global page options
            apntag.setPageOpts({
                member: 10653
            });
            apntag.anq.push(function () {
                apntag.defineTag({
                    tagId: parseInt(appNexusId),
                    sizes: [[16, 9]],
                    targetId: adwaysTargetId
                });
                apntag.loadTags();
            });
        }); 
        
        apntag.anq.push(function () {      
            /** keywords */
            apntag.setKeywords(adwaysTargetId,{ adways_debug_kw : 'debug' });
            var tab = [];
            tab.push('debug');
            apntag.setKeywords(adwaysTargetId,{ adways_debug_tab_kw : tab });
            
            var findKeywords = function(strToCheck) {
                var returnKeyword = [];
                var strToCheck = strToCheck.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                for (var id in adwKeywords) {
                    var keywordsCollection = adwKeywords[id];
                    var keywordsList = keywordsCollection.associated_keywords.split(',');
                    for (var j = 0; j < keywordsList.length; j++) {
                        var keyword = keywordsList[j].trim();
                        if (keyword !== '') {
                            var re = new RegExp(keyword.normalize("NFD").replace(/[\u0300-\u036f]/g, ""), 'i');
                            if (strToCheck.match(re)) {
                                returnKeyword.push(keywordsCollection.keyword);
                                break;
                            }
                        }
                    }
                }
                return returnKeyword;
            };
            var keywordfound = [];
            if (typeof adwKeywords !== 'undefined' && adwKeywords !== null) {
                try {
                    keywordfound = findKeywords(window.document.title);
                } catch (e) {}
                /**Digiteka**/
                try {
                    if (location.hostname.match('ultimedia'))
                        keywordfound = findKeywords(window.parent.dtkPlayer.vinfos.video.title);
                } catch (e) {}
                /**fin Digiteka**/
                /**Pepsia**/
                try {
                    if (window.document.title.match('Pepsia'))
                        keywordfound = findKeywords(document.getElementById('player_title').innerText);
                } catch (e) {}
                /**fin Pepsia**/
            }
            if (keywordfound.length>0) {                                
                apntag.setKeywords(adwaysTargetId,{adways_keywords: keywordfound});
            }       

            if (typeof that.adwConfig.videoslot !== "undefined" && that.adwConfig.videoslot !== "") {
                var videoslot = that.adwConfig.videoslot;
                if (typeof videoslot.iab !== "undefined" && videoslot.iab !== "") {
                    apntag.setKeywords(adwaysTargetId,{adways_iab: videoslot.iab});
                }
                if (typeof videoslot.options !== "undefined" && videoslot.options !== null && 
                    typeof videoslot.options.enableSendDomain !== "undefined" && videoslot.options.enableSendDomain) {
                    var kw_adways_domain = window.location.hostname;
                    if (typeof videoslot.options.sendDomainFromPartners !== "undefined" && videoslot.options.sendDomainFromPartners
                        && typeof videoslot.x_domain !== "undefined" && videoslot.x_domain !== "") {
                        kw_adways_domain = videoslot.x_domain;
                    }
                    apntag.setKeywords(adwaysTargetId,{adways_domain: encodeURIComponent(kw_adways_domain)});
                }
            }
            if (that.adwConfig.p2s !== null) {
                var p2s = that.adwConfig.p2s;
                var playerSize = p2s.getPlayerSize().valueOf();
                var width = playerSize[0];
                var height = playerSize[1];
                if(width !== null && !isNaN(width) && width > 0 && height !== null && !isNaN(height) && height > 0) {
                    var ratio = "169";
                    var actualRatio = Math.ceil(width/height*100)*1.0/100;
                    if(actualRatio>1) {
                        if((actualRatio) < (16/9) && (actualRatio) >= (4/3) ) {
                            ratio = "43";
                        } else if((actualRatio) < (4/3) && (actualRatio) >= (1) ) {
                            ratio = "11";
                        }
                    } else if (width === height) {
                        ratio= "11";
                    } else {
                        actualRatio = Math.ceil(height/width*100)*1.0/100;
                        ratio = "916";
                        if((actualRatio) < (16/9) && (actualRatio) >= (4/3) ) {
                            ratio = "34";
                        } else if((actualRatio) < (4/3) && (actualRatio) >= (1) ) {
                            ratio = "11";
                        }                                        
                    }
                    apntag.setKeywords(adwaysTargetId,{adways_ratio: ratio});
                }
            }
            /** end keywords */
        }); 
        var myDiv = w.document.createElement('div');
        myDiv.style.position = "absolute";
        myDiv.style.top = "0";
        myDiv.style.left = "0";
        myDiv.style.width = "0%";
        myDiv.style.height = "0%";
        myDiv.id = adwaysTargetId;
        w.document.body.appendChild(myDiv);
        apntag.anq.push(function () {
//            console.log("adwdebug push ");
            apntag.showTag(adwaysTargetId);
            });
    };
    if(this.adwConfig.addCalls.length > 0 && this.adwConfig.addCalls[0]['indice'] > -1) {
//        console.log("adwdebug vpaid fallback",this.adwConfig.addCalls.length, this.adwConfig.addCalls[0]['indice'], this.adwConfig.addCalls);
        this.tracker.sendData({event_type: 'state', event_name: 'fallback' + this.adwConfig.addCalls[0]['indice']});
    }
    else {
//        console.log("adwdebug vpaid adCall",this.adwConfig.addCalls);
        this.tracker.sendData({event_type: 'state', event_name: 'adCall'});
    }
    this._loadVPAIDAd();
};if (window.adways === undefined) {
    window.adways = new Object();
}

if (window.adways.playerHelpers === undefined) {
    window.adways.playerHelpers = new Object();
}

window.adways.playerHelpers.PlayerDetector = function() {
    this._detectors = new Array();
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.JWPlayer7Detector()
    });
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.YoutubeDetector()
    });
    this._detectors.push({
        priority: 50,
        detector: new window.adways.playerHelpers.YoutubePMDetector()
    });
    this._detectors.push({
        priority: 50,
        detector: new window.adways.playerHelpers.BrightcoveDetector()
    });
    this._detectors.push({
        priority: 60,
        detector: new window.adways.playerHelpers.JWPlayer6Detector()
    });
    this._detectors.push({
        priority: 50,
        detector: new window.adways.playerHelpers.JWPlayer8Detector()
    });
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.VimeoDetector()
    });
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.DailymotionDetector()
    });
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.DailymotionSDKDetector()
    });
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.DailymotionComDetector()
    });
    this._detectors.push({
        priority: 50,
        detector: new window.adways.playerHelpers.OoyalaDetector()
    });
    this._detectors.push({
        priority: 50,
        detector: new window.adways.playerHelpers.OoyalaV4Detector()
    });
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.BrainsonicDetector()
    });
    this._detectors.push({
        priority: 30,
        detector: new window.adways.playerHelpers.VideoJSDetector()
    });
    this._detectors.push({
        priority: 20,
        detector: new window.adways.playerHelpers.HTML5Detector()
    });
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.FranceTVDetector()
    });
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.thePlatformDetector()
    });
    this._detectors.push({
        priority: 80,
        detector: new window.adways.playerHelpers.ViouslyDetector()
    });
    this._detectors.push({
        priority: 70,
        detector: new window.adways.playerHelpers.ViouslyPlayerDetector()
    });
    this._detectors.push({
        priority: 100,
        detector: new window.adways.playerHelpers.VidazooDetector()
    });
    this._sortDetectors();
};

window.adways.playerHelpers.PlayerDetector.prototype.playerClassFromPlayerAPI = function(playerAPI) {
    var detected = false;
    for (var i = 0; i < this._detectors.length && !(detected = this._detectors[i].detector.detect(playerAPI)); i++)
        ;
    if (detected) {
        return this._detectors[i].detector.getPlayerClass();
    }
    return "noplayer";
};

window.adways.playerHelpers.PlayerDetector.prototype._sortDetectors = function() {
    this._detectors.sort(function(a, b) {
        if (a.priority < b.priority) {
            return 1;
        } else if (a.priority > b.priority) {
            return -1;
        }
        return 0;
    });
};

window.adways.playerHelpers.JWPlayer7Detector = function() {
    this._playerClass = "jwplayer7";
};

window.adways.playerHelpers.JWPlayer7Detector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.JWPlayer7Detector.prototype.detect = function(playerAPI) {
    if (playerAPI.version !== undefined && typeof playerAPI.version !== 'function') {
        var ret = playerAPI.version.match('7\..*\.jwplayer\..*');
        if (ret !== null && ret[0] === playerAPI.version) {
            return true;
        }
    }
    return false;
};

window.adways.playerHelpers.JWPlayer8Detector = function() {
    this._playerClass = "jwplayer8";
};

window.adways.playerHelpers.JWPlayer8Detector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.JWPlayer8Detector.prototype.detect = function(playerAPI) {
    if (playerAPI.version !== undefined && typeof playerAPI.version !== 'function') {
//        console.log("JWPlayer8Detector", playerAPI.version);
        var ret = playerAPI.version.match('8\..*\.jwplayer\..*');
        if (ret !== null && ret[0] === playerAPI.version) {
            return true;
        }
    }
    return false;
};
//

window.adways.playerHelpers.YoutubeDetector = function() {
    this._playerClass = "youtube";
};

window.adways.playerHelpers.YoutubeDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.YoutubeDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.getApiInterface !== undefined &&
            playerAPI.getVideoUrl !== undefined && typeof playerAPI.getVideoUrl === 'function' &&
            playerAPI.getVideoEmbedCode !== undefined && typeof playerAPI.getVideoEmbedCode === 'function') {
        var videoUrl = playerAPI.getVideoUrl();
        var videoEmbedCode = playerAPI.getVideoEmbedCode();
        var tmp1 = videoUrl.match('.*youtube.*\/watch?.*');
        var tmp2 = videoEmbedCode.match('.*src=.*www.youtube.com.*');
        if (tmp1 !== null && tmp2 != null &&
                tmp1[0] === videoUrl &&
                tmp2[0] === videoEmbedCode) {
            return true;
        }
    }
    return false;
};

//

window.adways.playerHelpers.YoutubePMDetector = function() {
    this._playerClass = "youtubePM";
};

window.adways.playerHelpers.YoutubePMDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.YoutubePMDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.src !== undefined && typeof playerAPI.src !== 'function') {
        var tmp = playerAPI.src.match('.*www.youtube.com/embed.*');
        if (tmp !== null && tmp[0] === playerAPI.src) {
            var tmp2 = playerAPI.src.match('.*enablejsapi.*');
            if (tmp2 !== null) {
                return true;
            }
        }
    }
    return false;
};

//

window.adways.playerHelpers.BrightcoveDetector = function() {
    this._playerClass = "brightcove";
};

window.adways.playerHelpers.BrightcoveDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.BrightcoveDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.toJSON !== undefined && typeof playerAPI.toJSON === 'function') {
        var json = playerAPI.toJSON();
        if (json['data-account'] !== undefined &&
                json['data-player'] !== undefined) {
            return true;
        }
    }
    return false;
};

//

window.adways.playerHelpers.JWPlayer6Detector = function() {
    this._playerClass = "jwplayer6";
};

window.adways.playerHelpers.JWPlayer6Detector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.JWPlayer6Detector.prototype.detect = function(playerAPI) {
    if (playerAPI.releaseState !== undefined &&
            playerAPI.setCurrentCaptions !== undefined &&
            playerAPI.registerPlugin !== undefined &&
            playerAPI.loadInstream !== undefined &&
            playerAPI.getLockState !== undefined) {
        return true;
    }
    return false;
};

//

window.adways.playerHelpers.VimeoDetector = function() {
    this._playerClass = "vimeo";
};

window.adways.playerHelpers.VimeoDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.VimeoDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.src !== undefined && typeof playerAPI.src !== 'function') {
        var tmp = playerAPI.src.match('.*player.vimeo.com.*');
        if (tmp !== null && tmp[0] === playerAPI.src) {
            return true;
        }
    }
    return false;
};

//

window.adways.playerHelpers.DailymotionDetector = function() {
    this._playerClass = "dailymotion";
};

window.adways.playerHelpers.DailymotionDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.DailymotionDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.src !== undefined && typeof playerAPI.src !== 'function' && typeof (playerAPI.apiReady) === "undefined") {
        var tmp = playerAPI.src.match('.*dailymotion.com/embed.*');
        if (tmp !== null && tmp[0] === playerAPI.src) {
            return true;
        }
    }
    return false;
};

window.adways.playerHelpers.DailymotionComDetector = function() {
    this._playerClass = "dailymotionsdk";
};

window.adways.playerHelpers.DailymotionComDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.DailymotionComDetector.prototype.detect = function(playerAPI) {
    if (typeof playerAPI.baseURI !== 'undefined') {
        var tmp = playerAPI.baseURI.match('.*dailymotion.com.*');
        if (tmp !== null && playerAPI.getElementsByClassName("dmp_Player").length > 0) {
            return true;
        }
    }
    return false;
};

window.adways.playerHelpers.DailymotionSDKDetector = function() {
    this._playerClass = "dailymotionsdk";
};

window.adways.playerHelpers.DailymotionSDKDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.DailymotionSDKDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.src !== undefined && typeof playerAPI.src !== 'function' && typeof (playerAPI.apiReady) !== "undefined") {
        var tmp = playerAPI.src.match('.*dailymotion.com/embed.*');
        if (tmp !== null && tmp[0] === playerAPI.src) {
            return true;
        }
    }
    return false;
};


window.adways.playerHelpers.OoyalaV4Detector = function() {
    this._playerClass = "ooyala";
};

window.adways.playerHelpers.OoyalaV4Detector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.OoyalaV4Detector.prototype.detect = function(playerAPI) {
    if (playerAPI.getCurrentItemClosedCaptionsLanguages !== undefined &&
            playerAPI.getBitratesAvailable !== undefined &&
            playerAPI.updateAsset !== undefined &&
            playerAPI.getPlayheadTime !== undefined &&
            playerAPI.getElementId !== undefined &&
            playerAPI.setCurrentItemEmbedCode === undefined) {
        return true;
    }
    return false;
};

//

window.adways.playerHelpers.OoyalaDetector = function() {
    this._playerClass = "ooyala";
};

window.adways.playerHelpers.OoyalaDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.OoyalaDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.getCurrentItemClosedCaptionsLanguages !== undefined &&
            playerAPI.getBitratesAvailable !== undefined &&
            playerAPI.getPlayheadTime !== undefined &&
            playerAPI.shouldDisplayCuePointMarkers !== undefined &&
            playerAPI.setCurrentItemEmbedCode !== undefined) {
        return true;
    }
    return false;
};

//

window.adways.playerHelpers.BrainsonicDetector = function() {
    this._playerClass = "brainsonic";
};

window.adways.playerHelpers.BrainsonicDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.BrainsonicDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.getVersion !== undefined && typeof playerAPI.getVersion === 'function') {
        var version = playerAPI.getVersion();
        var ret = version.match('brainsonic-.*');
        if (ret !== null && ret[0] === version) {
            return true;
        }
    }
    return false;
};

//

window.adways.playerHelpers.HTML5Detector = function() {
    this._playerClass = "html5";
};

window.adways.playerHelpers.HTML5Detector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.HTML5Detector.prototype.detect = function(playerAPI) {
    if (playerAPI.tagName !== undefined && typeof playerAPI.tagName !== 'function' &&
            playerAPI.paused !== undefined && typeof playerAPI.paused !== 'function') {
        if (playerAPI.tagName === "VIDEO" && playerAPI.src !== '') {
            return true;
        }
    }
    return false;
};

//

window.adways.playerHelpers.VideoJSDetector = function() {
    this._playerClass = "videojs";
};

window.adways.playerHelpers.VideoJSDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.VideoJSDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.contentEl !== undefined && typeof playerAPI.contentEl === 'function') {
        var tmp = playerAPI.contentEl().className.match('.*video-js.*');
//        if (tmp !== null && tmp[0] === playerAPI.contentEl().className) {
        if (tmp !== null && playerAPI.contentEl().className.indexOf(tmp[0]) > -1) {
            return true;
        }
        var tmp = playerAPI.contentEl().className.match(/vjs-tech/i);
        if (tmp !== null && playerAPI.contentEl().className.indexOf(tmp[0]) > -1) {
            return true;
        }
    }
    return false;
};

//

window.adways.playerHelpers.FranceTVDetector = function() {
    this._playerClass = "francetv";
};

window.adways.playerHelpers.FranceTVDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.FranceTVDetector.prototype.detect = function(playerAPI) {
    if (typeof playerAPI.getCurrentMedia === 'function' &&
            typeof playerAPI.getPlayerContainer === 'function' &&
            playerAPI.getPlayerContainer().length > 0 &&
            playerAPI.getPlayerContainer()[0].className.match(/jqp\-/) != null
            ) {
        return true;
    }
    return false;
};

//

window.adways.playerHelpers.thePlatformDetector = function() {
    this._playerClass = "theplatform";
};

window.adways.playerHelpers.thePlatformDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.thePlatformDetector.prototype.detect = function(playerAPI) {
    if (typeof playerAPI.controller === 'object' && 
            playerAPI.controller !== null &&
            typeof playerAPI.controller.widgetId === 'string' &&
            typeof playerAPI.controller.getReleaseState === 'function'
            ) {
        return true;
    }
    return false;
};

window.adways.playerHelpers.ViouslyDetector = function() {
    this._playerClass = "viously";
};

window.adways.playerHelpers.ViouslyDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.ViouslyDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.parentNode 
            && playerAPI.parentNode.id
            && playerAPI.parentNode.id == "player"
            && playerAPI.ownerDocument.body.className.match("player-body")
            && playerAPI.ownerDocument.body.className.match("player-state-")
            && playerAPI.ownerDocument.getElementById("controls")) {
        this._playerAPI = playerAPI;
        return true;
    }
    return false;
};

window.adways.playerHelpers.ViouslyPlayerDetector = function() {
    this._playerClass = "viously";
};

window.adways.playerHelpers.ViouslyPlayerDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.ViouslyPlayerDetector.prototype.detect = function(playerAPI) {
    try {
        var players = playerAPI.getElementsByClassName('c-player');
        if(players.length > 0 &&  players[0].id == 'player' && players[0].childNodes.length>0 && players[0].childNodes[0].tagName.toLowerCase() === 'video') {
            this._playerAPI = players[0].childNodes[0];        
            return true;
        }
        return false;
    } catch (e) {
        return false;
    }
};

window.adways.playerHelpers.VidazooDetector = function() {
    this._playerClass = "vidazoo";
};

window.adways.playerHelpers.VidazooDetector.prototype.getPlayerClass = function() {
    return this._playerClass;
};

window.adways.playerHelpers.VidazooDetector.prototype.detect = function(playerAPI) {
    if (playerAPI.parentNode 
            && playerAPI.parentNode.className.match("sbt-placeholder")) {
        this._playerAPI = playerAPI;
        return true;
    }
    return false;
};var adwKeywordsCollection = {"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords?filter-online=1\u0026page=1"},"first":{"href":"https:\/\/services.adways.com\/keywords?filter-online=1"},"last":{"href":"https:\/\/services.adways.com\/keywords?filter-online=1\u0026page=1"}},"_embedded":{"collection":[{"name":"CDiscount Janvier 2020","keyword":"cdiscount2020","value":"1","associated_keywords":"solde, ventes priv\u00e9es, black friday, bose, dyson, iphone, ipod, apple, reconditionn\u00e9, voyage, vacances, vacance, soldes, r\u00e9ductions, r\u00e9duction, bonnes affaires, bonne affaire, discount, outlet, reduction, solde hiver, soldes hiver, bons plans, bon plan, promos du jour, promotions, promos, promo du jour, promo, plans du jour, plan du jour, soldes 2020, solde 2020, vente priv\u00e9e, offres choc, offres du jour, offres du moment, offre du jour, offres du jour, dates soldes, mercredi soldes, mercredi solde, premi\u00e8re d\u00e9marque, seconde d\u00e9marque, troisi\u00e8me d\u00e9marque, 2\u00e8me d\u00e9marque, 3\u00e8me d\u00e9marque, meilleures offres, r\u00e9duction exceptionnelle, affaires, affaires, affaire du jour, affaires du moment, affaires du jour","balises":"","online":"1","state":1,"id":"13","created":"2020-01-03 12:45:54","author":2426,"updated":"2020-01-08 13:53:51","updator":2758,"level":null,"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords\/13"}}},{"name":"Test Sublime DV 360","keyword":"testdv360sublime","value":"1","associated_keywords":"La taxe d\u0027habitation bien","balises":"","online":"1","state":1,"id":"12","created":"2019-12-09 16:58:15","author":2426,"updated":"2019-12-09 16:58:15","updator":2426,"level":null,"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords\/12"}}},{"name":"Bel Boursin Ap\u00e9ritif D\u00e9cembre 2019","keyword":"belboursinaperitif2019","value":"1","associated_keywords":"vin, amuse-bouche, ap\u00e9ro, vermouth, alcool, terrasse, amer, ap\u00e9ritif g\u00e9ant, breuvage, d\u00e9coction, liquide, parfait, rafra\u00eechissement, tisane, repas, digestif, entr\u00e9e, servi, app\u00e9tit, vin blanc, aliment, ap\u00e9ro g\u00e9ant, Carthag\u00e8ne, drink, mise en bouche, philtre, recette, Boursin, liqueur, fromage, kir, pastis, boisson alcoolis\u00e9e, charcuterie, anis\u00e9, bitter, consommation, \u00e9lixir, mistelle, potion, savouries, boisson, amuse-gueule, tapas, boire, dessert, absinthe, ap\u00e9ricube, boisson ap\u00e9ritive, convivialit\u00e9, garniture d\u0027ap\u00e9ritif, nectar, purgatif, sudorifique, martini, jack daniels, beerpong, beer pong","balises":"","online":"1","state":1,"id":"11","created":"2019-11-29 13:58:59","author":2426,"updated":"2019-11-29 13:58:59","updator":2426,"level":null,"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords\/11"}}},{"name":"InterMarch\u00e9 Estimations Gratin Novembre 2019","keyword":"intermarchegratinnov2019","value":"0","associated_keywords":"gratin, hachis parmentier, crumble, g\u00e2teau, dessert, gateau","balises":"","online":"1","state":1,"id":"10","created":"2019-11-18 13:44:59","author":2426,"updated":"2019-11-18 13:44:59","updator":2426,"level":null,"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords\/10"}}},{"name":"Club Med Novembre D\u00e9cembre 2019","keyword":"clubmednov2019","value":"1","associated_keywords":"vacance, islande, hotel, h\u00f4tel, vacances, luxe, s\u00e9jour, sejour, ski, location, chalet, asie, bali, malaisie, maldive, caraibe, cara\u00efbe, plage, soleil, janeiro, br\u00e9sil, bresil, club med, cancun, mexique, noce, punta cana, golf, guadeloupe, indien, bahamas, alpes, alpe, grand massif, yucatan, pacific, sanya, flaine, m\u00e9ribel, meribel, weekend, phuket, japon, s\u00e9jour, tignes, chamonix, val thorens, porte du soleil, snowboard, luge, avoriaz, voyage, avion, thailande, cambodge, philippines, italie, espagne, portugal, columbus, ile maurice, ile de la r\u00e9union, martinique, vosges, mont blanc, indon\u00e9sie, paradiski, all inclusive, dubai, duba\u00ef, croisi\u00e8re, iles, majorque, passeport, visa, road trip, jet priv\u00e9, valise, pyramide, sicile, forfait, tire fesse, australie, afrique, etna, archipel, plong\u00e9e, caraibe, cara\u00efbe, riche, island, bateau, voilier, jetski, jet-ski, plan\u00e8te, tropical, floride, trump tower, cocktail, casino, las vegas, palawan, kuta","balises":"","online":"1","state":1,"id":"9","created":"2019-11-15 09:03:57","author":2426,"updated":"2019-11-29 09:08:49","updator":2426,"level":null,"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords\/9"}}},{"name":"Amazon Prime Video Oct Nov 2019","keyword":"amazonprimevideooct2019","value":"1","associated_keywords":"The Boys, Carnival Row, Good omens, Hanna, American Gods, Good doctor , Vikings, Jack Ryan, The terror, Fleabag, Varane, Into the Dark, The Bold Type, The Grand Tour, Man in the High Castle, The Marvelous Mrs Maisel, Undone, modern love, The tick, The expanse, Savage x Fenty, The Widow, Dawson, Peaky Blinders, Raising Dion, Big Mouth, Plan C\u0153ur, Living with yourself, El Camino, Casa De Papel, Top Boy, Elite, Criminal, Stranger Things, Lucifer, Mindhunter, 13 reasons Why, Mouche, Les sauvages, Fear of the walking dead, This is us, Killing Eve, Snowfall, Brooklyn nine-nine, This is us, The Hot Zone, The Affair, Years and Years, Deadly Class, Engrenages, Hippocrate, S\u00e9rie, Amazon Prime Video, Netflix, Canal+, OCS, Canal+ S\u00e9rie, SVOD, Saison, Action, Science-Fiction, Apple TV+, Disney+, Warner TV, T\u00e9l\u00e9vision, Salto, Streaming, SFR Play, Amazon Prime, plan coeur, friends, film, vod, acteur, \u00e9pisode, episode","balises":"","online":"1","state":1,"id":"8","created":"2019-10-30 10:31:12","author":2426,"updated":"2019-11-18 17:56:13","updator":2426,"level":null,"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords\/8"}}},{"name":"Bouygues Telecom Noel 2019","keyword":"btnoel","value":"0","associated_keywords":"\u00e9change, client, Amazon, service, drive, elle, shopper, familly shopper, shopping noel, No\u00ebl, offrir, souvenir, bague, p\u00e8re No\u00ebl, Saint-Valentin, sapin, g\u00e9n\u00e9rosit\u00e9, bracelet, v\u00eatement, lib\u00e9ralit\u00e9, commission, fian\u00e7ailles, surpasse, inestimable, apport\u00e9, donner, remercier, parrain, aide, avantage, charit\u00e9, courtage, dotation, t\u00e9l\u00e9phonique, appareil, fixe, iPhone, haut-parleur, num\u00e9ro, Nokia, GPS, transmettre, op\u00e9ratrice, annuaire, sim, voie de communication, usager, clavier, ordinateur, joindre, Apple, intelligents, distance, hygiaphone, ADSL, fil, t\u00e9l, kit mains libres, portatif, tactile, cam\u00e9ra, Huawei, cadeau , shopping , retail, internet, cadeaux, surprise, coffret, anniversaire, pr\u00e9cieux, jouet, t\u00e9l\u00e9conseiller, puce, f\u00eate, bo\u00eete, attentions, munificence, distribuer, fiscal, ambassadeur, re\u00e7us, boucles d\u0027oreilles, emballer, remporter, plaisir, allocation, concession, donation, t\u00e9l\u00e9phonie, cadran, cellulaire, Bluetooth, t\u00e9l\u00e9graphe, t\u00e9l\u00e9communication, Samsung, joignable, autocommutateur, r\u00e9cepteur, Bell, sonner, \u00e9lectrique, appel, connecter, tablette, num\u00e9ro de t\u00e9l\u00e9phone, conversation, iOS, espionner, phone, Beyonc\u00e9, minitel, t\u00e9l\u00e9phone mobile, microphone, radio, vente, connexion, op\u00e9rateur, authentification, t\u00e9l\u00e9com, friture, Windows Phone, Telus, clip, Google, Motorola, appareil t\u00e9l\u00e9phonique, Britney Spears, \u00e9trennes, aum\u00f4ne, \u00e9changer, offre, corbeille, remerciement, bouquets, friandise, r\u00e9veillon, amoureux, arbre de No\u00ebl, bienfait, contribution, donn\u00e9e, faveur, sonnerie, communication, portable, \u00e9couteur, bigophone, Android, Wi-Fi, raccrocher, fax, r\u00e9seau, t\u00e9l\u00e9phoniste, tonalit\u00e9, GSM, chargeur, \u00e9cran, courriel, t\u00e9l\u00e9phone portable, coup, PTT, gaga, portabilit\u00e9, bonjour, paparazzi, abonn\u00e9, parleur, r\u00e9veiller, ordiphone, e-mail, utilisateur, destinataire, USB, Jean-Louis Aubert, t\u00e9l\u00e9charger, bignou, combin\u00e9 t\u00e9l\u00e9phonique, grelot, nomophobie, Siri, t\u00e9l\u00e9phonite, visiophone, port, Skype, bureau, filaire, verrouillage, reli\u00e9, num\u00e9ro d\u0027appel, phonetel, taxiphone, coutume, r\u00e9compense, cadonner, offrande, maman, argent, noce, tube, cadeau de No\u00ebl, envoi, ristourne, guise, Valentin, ch\u00e8que, g\u00e2teau, somptueux, ch\u00e2le, attribution, bont\u00e9, cotisation, donneur, gracieuset\u00e9, mobile, smartphone, SMS, combin\u00e9, messagerie, c\u00e2ble, cabine t\u00e9l\u00e9phonique, recharge, message, texto, natel, bigo, service, voix, interphone, internet, transfert, forfait, adaptateur, g\u00e9olocalisation, t\u00e9l\u00e9commande, branchement, r\u00e9pondeur, BlackBerry, num\u00e9ro t\u00e9l\u00e9phonique, poste t\u00e9l\u00e9phonique, t\u00e9l, transmetteur, walkman, carte SIM, Louis Bertignac, Xiaomi, mariah carey, ski, snowboard, snow, d\u00e9co, deco, p\u00e8re, ou, le, de","balises":"","online":"1","state":1,"id":"7","created":"2019-10-29 15:36:14","author":2426,"updated":"2019-12-24 13:58:25","updator":2758,"level":null,"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords\/7"}}},{"name":"BlackList","keyword":"blackList","value":"1","associated_keywords":"attaque, attentat, fusillade, kidnapping, maelys, ma\u00eblys, suicide, zemmour, terrorisme, terroriste, viol, crime, assassinat, mort, morte, tuerie, incendie, violence, drogue, d\u00e9c\u00e9d\u00e9, d\u00e9c\u00e9d\u00e9e, chirac, bombe, explosion, charlie, daech, daesh, djihad, djihadiste, bataclan, funeraille, incendie, prison, menace, tueur, guerre, g\u00e9nocide, genocide, combat, criminel, assassinat, assassin, violation, isis, Sophie Ferjani, 2413132","balises":"","online":"1","state":1,"id":"2","created":"2019-09-24 09:17:49","author":17,"updated":"2019-12-20 13:58:32","updator":2426,"level":null,"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords\/2"}}},{"name":"Bouygues Telecom Moovers","keyword":"btMoovers","value":"1","associated_keywords":"jardin, jardinage, potager, fleurs, louer, biblioth\u00e8que, transfert, web, internaute, r\u00e9seau, ordinateur, courriel, op\u00e9rateur, mobile, fibre, t\u00e9l\u00e9phonie, tv, t\u00e9l\u00e9vision, telecom, messagerie, marteau, v\u00e9randa, balcon, terrasse, toit, locatif, propri\u00e9taire, bailleur, bail, agence, copropri\u00e9t\u00e9, immeuble, abonnement, achat, appartement, ascenseur, box, chambre, check, check-list, checklist, coloc, colocation, demenagement, d\u00e9menager, dernier, duplex, d\u00e9m\u00e9nagement, d\u00e9m\u00e9nagements, d\u00e9m\u00e9nager, f3, f4, fonci\u00e8re, immobili\u00e8res, internet, list, location, maison, meubl\u00e9, meubl\u00e9e, offre, pi\u00e8ce, pi\u00e8ces, proximit\u00e9, studio, taxe, transports, ventes, \u00e9tage, immobilier, mobilier, d\u00e9m\u00e9nageur, demenageur, transporteur, renovation, r\u00e9novation, cave, grenier, cuisine, peindre, peinture, ouvrier, fa\u00e7ade, ravalement, isolation, cartons, habitation, villa, notaire, loyer, ameublement, free, orange, bouygues, sfr, fai, ou","balises":"","online":"1","state":1,"id":"1","created":"2019-09-24 09:13:42","author":17,"updated":"2019-12-19 16:06:54","updator":2426,"level":null,"_links":{"self":{"href":"https:\/\/services.adways.com\/keywords\/1"}}}]},"page_count":1,"page_size":9999,"total_items":9,"page":1};    var adwKeywords = null;
    if (adwKeywordsCollection['_embedded'] && adwKeywordsCollection['_embedded']['collection'] &&
        adwKeywordsCollection['_embedded']['collection'].length>0) {
        adwKeywords = adwKeywordsCollection['_embedded']['collection'];
    }
    var analyticsScriptTag = null;
    var analyticsLibLoaded = false;
    var adwaysLibScriptTag = null;
    var adwaysLibLoaded = false;
    var delegateScriptTag = null;
    var delegateParams = new Object();

    VPAIDWrapper = function () {
        this.VPAIDVersion = "2.0";
        this._slot = null;
        this._videoSlot = null;
        this.listeners = new Array();
        this.delegateClassname = '';
        this.playerDetectorRes = '';
        this.startAdDispatched = false;
        this.readyToLaunch = false;
        this.locatePlayer = null;
        var that = this;
        this.vpaidParameters = {}; // Contient toutes les query données au vpaid

        var queryStr = 'publicationId=Fsjjnzg&forceVideoMethod=standard';
        var queryArr = queryStr.replace('?', '').split('&');

        for (var q = 0; q < queryArr.length; q++) {
            var qArr = queryArr[q].split('=');
            this.vpaidParameters[qArr[0]] = qArr[1];
        }


        // tracking part
        this.isLocalHost = function () {
            var hostname = window.location.hostname;
            return !!(hostname === 'localhost' || hostname === '[::1]' || hostname.match(/^127(?:\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3}$/) || hostname.match(/^10(?:\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3}$/));
        };
        this.trackerObj = {
            record_interface: "generic",
            creative_format: "glBkVjd",
            creative_id: "Fsjjnzg",
            random_number: function () {
                return Math.random();
            },
            send_tracker_init: false
        };
        if (!this.isLocalHost())
            this.trackerObj['force_protocol'] = "https";
        this.trackerObj['nc'] = 0;

        this.analyticsScriptTagLoadCb = function () {
            analyticsScriptTag.removeEventListener("load", that.analyticsScriptTagLoadCb);
            analyticsLibLoaded = true;
            if (that.vpaidParameters.publisher !== null && that.vpaidParameters.publisher !== '') {
                that.trackerObj.x_domain = that.vpaidParameters.publisher;
            }
            that.tracker = new window.adways.analytics.Tracker(that.trackerObj);
            that.loadAd();
        };
        this.adwaysLibScriptTagLoadCb = function () {
            adwaysLibLoaded = true;
            adwaysLibScriptTag.removeEventListener("load", that.adwaysLibScriptTagLoadCb);
            that.loadAd();
        };
        this.delegateScriptTagLoadCb = function () {
            if (delegateScriptTag !== null)
                delegateScriptTag.removeEventListener("load", that.delegateScriptTagLoadCb);            
            that.checkTargeting();
        };
        // end tracking part
    };

    VPAIDWrapper.prototype.loadLib = function () {
        if (typeof window.adways === "undefined" || typeof window.adways.analytics === "undefined") {
            analyticsScriptTag.addEventListener("load", this.analyticsScriptTagLoadCb);
        } else {
            analyticsLibLoaded = true;
            if (this.vpaidParameters.publisher !== null && this.vpaidParameters.publisher !== '') {
                this.trackerObj.x_domain = this.vpaidParameters.publisher;
            }
            this.tracker = new window.adways.analytics.Tracker(this.trackerObj);
        }
        if ((typeof window.adways.scw === "undefined") && adwaysLibScriptTag !== null) {
//            console.log("adwdebug : loadLib adways");
            adwaysLibScriptTag.addEventListener("load", this.adwaysLibScriptTagLoadCb);
        } else {
            adwaysLibLoaded = true;
        }

        this.loadAd();
    };

    VPAIDWrapper.prototype.prepareCrea = function (environmentVars) {
        this._videoSlot = environmentVars.videoSlot;
        
         if(environmentVars.p2s && environmentVars.s2p && environmentVars.delegate){
            this.p2s = environmentVars.p2s;
            this.s2p = environmentVars.s2p;
            this.delegate = environmentVars.delegate;
            this.delegateClassname = this.delegate.constructor.name;
        } else if (typeof environmentVars.slot !== "undefined" && environmentVars.slot !== null && 
                environmentVars.slot.p2s && environmentVars.slot.s2p && environmentVars.slot.delegate) {
            this.p2s = environmentVars.slot.p2s;
            this.s2p = environmentVars.slot.s2p;
            this.delegate = environmentVars.slot.delegate;
            this.delegateClassname = this.delegate.constructor.name;
        }
        
        if(environmentVars.domain){
            this.domain = environmentVars.domain;
        } else if (typeof environmentVars.slot !== "undefined" && environmentVars.slot !== null && 
                environmentVars.slot.domain) {
            this.domain = environmentVars.slot.domain;
        }
        
        if (typeof environmentVars.slot !== "undefined" && environmentVars.slot !== null) {
            this._slot = environmentVars.slot;
        } else {
            if (typeof (this._videoSlot.getContainer) == "function" && this._videoSlot.getContainer() !== null) {
                this._slot = this._videoSlot.getContainer();
            } else {
                this._slot = this._videoSlot;
            }
        }      
        //Wibbitz hack
        try {
            var parentVideoSlot = window.frameElement;
            while (parentVideoSlot.getElementsByClassName("sbt-placeholder").length < 1 && parentVideoSlot.parentNode !== null) {
                parentVideoSlot = parentVideoSlot.parentNode;
            }
            if (parentVideoSlot.getElementsByClassName("sbt-placeholder").length > 0) {
                var container = parentVideoSlot.getElementsByClassName("sbt-placeholder")[0];     
//                console.log("sbt-placeholder found", container); 
                if (container.getElementsByTagName("video").length > 0) {
                    this._videoSlot = container.getElementsByTagName("video")[0];
//                    console.log("_videoSlot found", this._videoSlot);
                    this._slot = this._videoSlot; 
                }
            }
        } catch (e) {
//            console.log("sbt-placeholder not found");
        }
        //end Wibbitz hack
        //6play hack
        try {
            if (location.hostname.match('6play.fr')) {
                this._videoSlot = this._videoSlot.ownerDocument.getElementsByTagName('video')[0];        
                this._slot = this._videoSlot; 
            }                   
        } catch (e) {
            console.log("initAd 6play failed");
        }
        //end 6play hack
        //developer.jwplayer.com hack
        try {
            if(location.hostname.match('developer.jwplayer.com')){     
                var iframes = this._videoSlot.offsetParent.getElementsByClassName('jw-vpaid-iframe');
                if(iframes.length>0) {
                    iframes[0].style.setProperty("width", "100%", "important");
                    iframes[0].style.setProperty("height", "100%", "important");
                    const ro = new ResizeObserver(entries => {
                        iframes[0].style.setProperty("width", "100%", "important");
                        iframes[0].style.setProperty("height", "100%", "important");
                      });
                    ro.observe(iframes[0]);
                }
            }                   
        } catch (e) {
            console.log("initAd developer.jwplayer.com failed");
        }

        //potin hack
        try {
            var parentWindow = window;
            while ((parentWindow.document.getElementsByClassName('unmute_mobile_limitation').length<1 && 
                    parentWindow.document.getElementById('unmute_mobile_limitation') === null)
                && parentWindow !== parentWindow.parent)
                parentWindow = parentWindow.parent;
            if(parentWindow.document.getElementsByClassName('unmute_mobile_limitation').length>0) {
                var muteDiv = parentWindow.document.getElementsByClassName('unmute_mobile_limitation')[0];  
                muteDiv.style.setProperty("display", "none", "important");
            }
            if(parentWindow.document.getElementById('unmute_mobile_limitation') !== null) {
                var muteDiv = parentWindow.document.getElementById('unmute_mobile_limitation');  
                muteDiv.style.setProperty("display", "none", "important");
            }
        } catch (e) {
            console.log("initAd potin failed");
        }
        //end potin hack
    };

    VPAIDWrapper.prototype.loadAd = function () {
        if (analyticsLibLoaded && adwaysLibLoaded) {
            if(this.s2p == null || this.p2s == null || this.delegate == null){
                var playerDetector = new adways.playerHelpers.PlayerDetector();
                this.playerDetectorRes = playerDetector.playerClassFromPlayerAPI(this._videoSlot);
    //            console.log("loadAd", this.playerDetectorRes);
                if (this.playerDetectorRes === "noplayer") {
                    this._videoSlot = new Object();
                    this._videoSlot.overlay = this._slot;
                    this.delegateUrl = "https://play.adpaths.com/libs/delegates/noplayer.js";
                    this.delegateClassname = "NoPlayerDelegate";
                    this.buildDelegate();
                } else {
                    this.requestPlayerClassFromJSConstant();
                }
            } else {
                this.playerDetectorRes = this.delegate.constructor.name;
                this.delegateScriptTagLoadCb();
            }
        }
    };

    VPAIDWrapper.prototype.buildDelegate = function () {
//        console.log("adwdebug : buildDelegate");
        delegateScriptTag = document.createElement("script");
        if (typeof (adways.tweaks.isIE) === "number" && adways.tweaks.isIE <= 8)
            delegateScriptTag.type = "text/javascript";
        else
            delegateScriptTag.type = "application/javascript";
        var delegateScriptTagSrc = this.delegateUrl;
        delegateScriptTag.src = delegateScriptTagSrc;
        adways.misc.html.addEventListener(delegateScriptTag, "load", this.delegateScriptTagLoadCb);
        document.getElementsByTagName("head")[0].appendChild(delegateScriptTag);
        return 1;
    };

    VPAIDWrapper.prototype.requestPlayerClassFromJSConstant = function () {
//        console.log("adwdebug : requestPlayerClassFromJSConstant");
        if (this.playerDetectorRes === "")
            return -1;
        var playerClassGetURL = "https://d1afeohcmx2qm4.cloudfront.net/player-class?filter-js_constant=" + this.playerDetectorRes.toUpperCase();

        var playerClassRequest = new adways.ajax.Request();
        playerClassRequest.setURL(playerClassGetURL);
        playerClassRequest.setMethod("GET");
        playerClassRequest.addHeader("Accept", "application/json");
        playerClassRequest.setContentType("application/json");
        var that = this;
        var requestDoneListener = function (evt) {
            if (playerClassRequest !== null && playerClassRequest.getState() === adways.ajax.states.DONE) {
                playerClassRequest.removeEventListener(adways.ajax.events.STATE_CHANGED, requestDoneListener);
                var responseText = playerClassRequest.getResponseText();
                playerClassRequest = null;
                var responseParsed = null;
                responseParsed = JSON.parse(responseText);
                if (responseParsed["_embedded"] && responseParsed["_embedded"]["collection"]
                    && responseParsed["_embedded"]["collection"][0]) {
                    that.delegateUrl = responseParsed["_embedded"]["collection"][0]["delegate_url"];
                    if (!that.delegateUrl.match("^\/[\/\/]+")) {
                        if (that.delegateUrl[0] === "/") {
                            that.delegateUrl = that.delegateUrl.substr(1, that.delegateUrl.length);
                        }
                        that.delegateUrl = "https://play.adpaths.com/" + that.delegateUrl;
                    }

                    that.delegateClassname = responseParsed["_embedded"]["collection"][0]["delegate_classname"];
                    that.buildDelegate();
                }
            }
        };
        playerClassRequest.addEventListener(adways.ajax.events.STATE_CHANGED, requestDoneListener);
        playerClassRequest.load();
        return 1;
    };
    VPAIDWrapper.prototype.checkTargeting = function () {

        delegateParams[this.playerDetectorRes] = this.delegateClassname;

        var config = {};
        config.targeting = [{"domain":".","inventories":[{"creation_type":"vast","creation_url":"\/\/ib.adnxs.com\/ptv?id=16488128","creation_capping":0,"creation_fallbacks":[],"creation_backfill_number":0,"creation_backfills":[],"creation_minwidth":400,"creation_startat_type":"second","creation_startat":20,"creation_repeat":0,"creation_minduration_type":"remaining","creation_minduration":30,"creation_use_visibility":true,"creation_visibility_percent":95,"creation_visibility_timer":0,"creation_prevent_incontent":false,"creation_autoplay_allowed":false,"creation_mobileonly":false,"creation_desktoponly":false,"nb_sub_pages":0}]}];
        var docToSearch = document;
        var win = window;
        this.tracker.sendData({event_type: 'state', event_name: 'impression'});
        this.tracker.timeInitTrackerMC = Date.now();
        var domain = '';        
        if (typeof this.vpaidParameters.publisher !== 'undefined' && this.vpaidParameters.publisher !== null && this.vpaidParameters.publisher !== '') {
            domain = this.vpaidParameters.publisher;
        }        
        var myTargetizers = new Targetizer(win, config.targeting, domain);
        for (var i=0; i<myTargetizers.length; i++) {
            var myTargetizer = myTargetizers[i];
            var that = this;
            if (typeof myTargetizer.customCallbacks !== 'object') {
                myTargetizer.customCallbacks = new Object();
            }
            if (typeof myTargetizer.customCallbacks.dispatchAdImpression !== 'function') {
                myTargetizer.customCallbacks.dispatchAdImpression = function () {
                    that.dispatchEvent("AdImpression");
                };
            }
            if (typeof myTargetizer.customCallbacks.dispatchAdClickThru !== 'function') {
                myTargetizer.customCallbacks.dispatchAdClickThru = function () {
                    that.dispatchEvent("AdClickThru", "", "", false);
                };
            }
            if (typeof myTargetizer.customCallbacks.dispatchAdVideoStart !== 'function') {
                myTargetizer.customCallbacks.dispatchAdVideoStart = function () {
                    that.dispatchEvent("AdVideoStart");
                };
            }
            if (typeof myTargetizer.customCallbacks.dispatchAdVideoFirstQuartile !== 'function') {
                myTargetizer.customCallbacks.dispatchAdVideoFirstQuartile = function () {
                    that.dispatchEvent("AdVideoFirstQuartile");
                };
            }
            if (typeof myTargetizer.customCallbacks.dispatchAdVideoMidpoint !== 'function') {
                myTargetizer.customCallbacks.dispatchAdVideoMidpoint = function () {
                    that.dispatchEvent("AdVideoMidpoint");
                };
            }
            if (typeof myTargetizer.customCallbacks.dispatchAdVideoThirdQuartile !== 'function') {
                myTargetizer.customCallbacks.dispatchAdVideoThirdQuartile = function () {
                    that.dispatchEvent("AdVideoThirdQuartile");
                };
            }
            if (typeof myTargetizer.customCallbacks.dispatchAdVideoComplete !== 'function') {
                myTargetizer.customCallbacks.dispatchAdVideoComplete = function () {
                    that.dispatchEvent("AdVideoComplete");
                };
            }
            if (typeof myTargetizer.customCallbacks.dispatchAdSkipped !== 'function') {
                myTargetizer.customCallbacks.dispatchAdSkipped = function () {
                    that.dispatchEvent("AdSkipped");
                };
            }

            if (this.locatePlayer = new LocatePlayer(docToSearch, config, myTargetizer, this.tracker)) {
                try {
                    this._videoSlot.adwSlot = this._slot;
                    if (typeof this.vpaidParameters.publisher !== 'undefined' && this.vpaidParameters.publisher !== null && this.vpaidParameters.publisher !== '') {
                        this._videoSlot.x_domain = this.vpaidParameters.publisher;
                    }
                    if (typeof this.vpaidParameters.iab !== 'undefined' && this.vpaidParameters.iab !== null && this.vpaidParameters.iab !== '') {
                        this._videoSlot.iab = this.vpaidParameters.iab;
                    }
                    if (typeof this.vpaidParameters.customConfig !== 'undefined' && this.vpaidParameters.customConfig !== null && this.vpaidParameters.customConfig !== '') {
                        this._videoSlot.customConfig = this.vpaidParameters.customConfig;
                    }                    
                    var options = new Object();
                    options.enableSendDomain = false;
                    if (options.enableSendDomain)
                        options.sendDomainFromPartners = false;
                    
                    if (typeof this.p2s !== 'undefined' && this.p2s !== null)
                        options.p2s = this.p2s;
                    if (typeof this.s2p !== 'undefined' && this.s2p !== null)
                        options.s2p = this.s2p;
                    if (typeof this.delegate !== 'undefined' && this.delegate !== null)
                        options.delegate = this.delegate;
                    if (typeof this.delegateClassname !== 'undefined' && this.delegateClassname !== null)
                        options.delegateClassname = this.delegateClassname;         
                    this._videoSlot.options = options;
                } catch (e) {
                    console.log("adw slot failed", e);
                }
        //        this.locatePlayer.instantiateInteractivity([this.playerDetectorRes, this._videoSlot]);
                this.readyToLaunch = true;
                this.tryLaunchCrea();
            }
        }
    };

    VPAIDWrapper.prototype.tryLaunchCrea = function () {
//        console.log("adwDebug : tryLaunchCrea", this.startAdDispatched , this.readyToLaunch);
        if (this.startAdDispatched && this.readyToLaunch) {
            this.locatePlayer.instantiateInteractivity([this.playerDetectorRes, this._videoSlot]);
        }
    };

    VPAIDWrapper.prototype.initAd = function (width, height, viewMode, desiredBitrate, creativeData, environmentVars) {
        this.dispatchEvent("AdLoaded");
        this.prepareCrea(environmentVars);
        this.loadLib();
    };

    VPAIDWrapper.prototype.getAdLinear = function () {
//        return true;
        return false;
    };
    VPAIDWrapper.prototype.getAdDuration = function () {
        return 30;
    };
    VPAIDWrapper.prototype.getAdRemainingTime = function () {
        return 30;
    };
    VPAIDWrapper.prototype.stopAd = function (e, p) {
        this.dispatchEvent("AdStopped");
        this.destroy();
    };
    VPAIDWrapper.prototype.pauseAd = function () {
    };
    VPAIDWrapper.prototype.resumeAd = function () {
    };
    VPAIDWrapper.prototype.expandAd = function () {
    };
    VPAIDWrapper.prototype.getAdExpanded = function (val) {
    };

    VPAIDWrapper.prototype.getAdSkippableState = function (val) {
    };
    VPAIDWrapper.prototype.collapseAd = function () {
    };
    VPAIDWrapper.prototype.skipAd = function () {
    };
    VPAIDWrapper.prototype.startAd = function () {
//        console.log("adwDebug : startAd");
//        if(!this.startAdDispatched) {
        this.startAdDispatched = true;
        this.dispatchEvent("AdStarted");
//            console.log("adwDebug : real AdStarted");
        this.tryLaunchCrea();
//        }
    };
    VPAIDWrapper.prototype.handshakeVersion = function (version) {
        return this.VPAIDVersion;
    };

    VPAIDWrapper.prototype.getAdIcons = function () {
    };

    VPAIDWrapper.prototype.getAdWidth = function () {
//        return 0;
        return 640;
    };

    VPAIDWrapper.prototype.getAdHeight = function () {
//        return 0;
        return 360;
    };

    VPAIDWrapper.prototype.setAdVolume = function (val) {
    };
    VPAIDWrapper.prototype.getAdVolume = function () {
    };
    VPAIDWrapper.prototype.resizeAd = function (width, height, viewMode) {
    };

    VPAIDWrapper.prototype.subscribe = function (fn, evt, inst) {
        if (typeof (this.listeners[evt]) === "undefined")
            this.listeners[evt] = new Array();
        var tmpObj = new Object();
        tmpObj.fcn = fn;
        tmpObj.inst = (arguments.length > 2 ? inst : null);
        this.listeners[evt][this.listeners[evt].length] = tmpObj;
    };

    VPAIDWrapper.prototype.unsubscribe = function (evt) {
        try {
            if (typeof (this.listeners[evt]) !== "undefined")
                delete this.listeners[evt];
        }
        catch (e) {
            console.warn(e);
        }
    };

    VPAIDWrapper.prototype.dispatchEvent = function (evt) {
        var args = new Array();
        for (var i = 1; i < arguments.length; i++)
            args.push(arguments[i]);
        if (typeof (this.listeners[evt]) !== "undefined") {
            for (var i = 0; i < this.listeners[evt].length; i++) {
                this.listeners[evt][i].fcn.apply(this.listeners[evt][i].inst, args);
            }
        }
//        if (arguments.length > 0 && arguments[0] === 'AdImpression' && this.videoProp !== null)
//            this.videoProp.dispatchCustomImpression();
    };

    VPAIDWrapper.prototype.destroy = function () {
        if (typeof this.locatePlayer !== 'undefined' && this.locatePlayer !== null
            && typeof this.locatePlayer.myVastLoaderManager !== 'undefined' && this.locatePlayer.myVastLoaderManager !== null) {
            this.locatePlayer.myVastLoaderManager.clearAll();
        }
    };

    getVPAIDAd = function () {
        return new VPAIDWrapper();
    };

    if (typeof window.adways === "undefined") {
        try {
            var parentWindow = window;
            while (typeof parentWindow.adways === "undefined" && parentWindow !== parentWindow.parent)
                parentWindow = parentWindow.parent;
            if (typeof parentWindow.adways !== "undefined")
                window.adways = parentWindow.adways;
        } catch (err) {

        }
        if (window.adways === undefined)
            window.adways = new Object();
    }

    if (typeof window.adways.scw === "undefined") {
        adwaysLibScriptTag = window.document.createElement("script");
        adwaysLibScriptTag.type = "text/javascript";
        adwaysLibScriptTag.src = "https://play.adpaths.com/libs/scw/release.min.js";
        if (window.document.body !== null) {
            window.document.body.appendChild(adwaysLibScriptTag);
        } else if (window.document.head !== null) {
            window.document.head.appendChild(adwaysLibScriptTag);
        }
    }

    if (typeof window.adways.analytics === "undefined") {
        analyticsScriptTag = window.document.createElement("script");
        analyticsScriptTag.type = "text/javascript";
        analyticsScriptTag.src = "https://www.adwstats.com/sdk.js";
        if (window.document.body !== null) {
            window.document.body.appendChild(analyticsScriptTag);
        } else if (window.document.head !== null) {
            window.document.head.appendChild(analyticsScriptTag);
        }
    }

}(window));