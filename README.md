The [Predator Alert Tool for Facebook](https://apps.facebook.com/predator-alert-tool/) is designed for survivors of sexual assault and rape. It allows you to share information about people in your social network who may be dangerous without having to reveal your identity.

Using Predator Alert Tool for Facebook, you can:

* **[Talk about it](https://github.com/meitar/pat-facebook/wiki/User-Manual:Talk-About-It).** Contribute your story with as much or as little detail as you feel comfortable sharing.
* **[Decide who knows](https://github.com/meitar/pat-facebook/wiki/User-Manual:Decide-Who-Knows).** Control who gets to see your story and who doesn't. Display your identity only to the people you choose.
* **[Get support](https://github.com/meitar/pat-facebook/wiki/User-Manual:Get-Support).** Connect with friends who have had a bad experience with the same person you did.
* **[Hear about it](https://github.com/meitar/pat-facebook/wiki/User-Manual:Hear-About-It).** Find out about others' bad experiences with people you know.

# Overview

The canonical instance available for public use as a proof of concept is available at [apps.facebook.com/predator-alert-tool](https://apps.facebook.com/predator-alert-tool/)

This software is a social justice technology project designed to graft anti-rape culture features directly onto the way Facebook.com works. It is free and open source software unencumbered by patent, copyright, or other legal claims of ownership. (See the accompanying `LICENSE` file for legalese.)

## How it works

There are two major parts to the Predator Alert Tool for Facebook software. The first is a server-side Facebook app where Facebook users can share about an experience they had regarding any other Facebook user's behavior. The second is a browser-side tool that automatically searches for any stories about the people who show up on your Facebook timeline and newsfeed as you browse Facebook.com. If it finds any, the tool "red-boxes" links to those people's profiles and offers a link to the stories it found.

Both of these pieces work independently. You can use the Predator Alert Tool for Facebook app inside Facebook without ever installing the browser-side tool on your device, and you can use the browser-side tool without ever allowing the Facebook app access to your Facebook account. (However, in the latter case, the tool will only be able to find stories shared with everyone, not friends-only or other visibility-restricted stories.) Both tools are contained in this repository and are released to the public domain.

# User manual and documentation

Inside the `docs/` directory, you'll find both user and developer documentation. The same information is also available on [the Predator Alert Tool for Facebook project's wiki on GitHub](https://github.com/meitar/pat-facebook/wiki). That's also where you'll find answers to frequently asked questions.

# Reporting bugs and other issues

If you find a problem with this software, which can be defined as anything from "I did X but Y happened, which I did not expect" to "I think these words should be different because they don't clearly explain what's about to happen when I click this button," please check to make sure that your issue hasn't already been reported and, if it hasn't been, write a "bug report" so the issue can be addressed constructively.

The way you do this is:

1. Go to https://github.com/meitar/pat-facebook/issues and skim the open issues to see if one describing your issue has already been reported. If it has not already been reported, continue to the next step. If it has been, just leave a comment letting us know you ran into the issue as well.
2. Read "[How to write a good bug report](http://noverse.com/blog/2012/06/how-to-write-a-good-bug-report/)" if you haven't already done so.
3. Copy the template that the above post provides into the form at https://github.com/meitar/pat-facebook/issues/new (You will need to [sign up for a free GitHub.com account](https://github.com/signup/free) if you don't already have one.)
4. Fill in the template you copied, and submit the form.

# Supporting this project

This project is 100% volunteer-run. There are no paid developers. There is no staff.

There is also no budget.

It takes time, heart, and material resources to ensure that this software continues to function, much less is improved on over time. If you can afford to do so, please consider making a donation in the form of food or money to [its houseless, nomadic developer](https://github.com/meitar/) at [Cyberbusking.org](http://Cyberbusking.org). Thank you very much.

Read more about [how to help](https://github.com/meitar/pat-facebook/wiki/How-to-help) on our wiki.
