# Description
A lightweight dark/light mode switcher with a customizable Blocksy-compatible dark palette floating toggle button.

# Installation
Install the plugin via the Wordpress menu Plugins -> Add Plugin.

# Changing colors
Use the menu: Settings -> Theme Mode Switcher and set each individual color to your liking.


# Adding switch to Blocksy’s header
Go to Appearance → Customizer → Header Builder.
Add an HTML element where you want the switch (e.g. right side of header).
In the HTML content box, just put:

`[stms_toggle]`


# Styling the header toggle separately
In `simple-theme-toggle.css` (or in Customizer → Additional CSS) you can tweak the header version:

```css
/* Header version – inherits base .stms-toggle styles, override what you want */
.stms-toggle-header {
    position: static;      /* no fixed position in header */
    width: 2.4rem;
    height: 2.4rem;
    box-shadow: none;
    margin-left: 0.75rem;
    font-size: 1.4rem;
}

/* Example: remove floating behaviour when used in header only */
header .stms-toggle {
    position: static;
}
```
