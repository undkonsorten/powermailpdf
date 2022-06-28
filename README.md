# TYPO3 extension: PowermailPdf

## Description

The ** TYPO3 extension PowermailPdf** converts form data into a PDF document. After submitting the form, the PDF can be downloaded or attached to and email.

The TYPO3 extension **PowermailPdf** extends the [TYPO3 extension Powermail](https://github.com/einpraegsam/powermail).


![inline](Documentation/Images/TYPO3-Powermail2Pdf.png)

## Features

Add form data into a marker bases pdf template file and
1. Add a download link to the (filled) pdf or given file.
2. Attaches the file

## Screenshot

![Screenshot TYP03-Extension: PowermailPdf download PDF with form data](Documentation/Images/thx.png)

## Requirements

* typo3/cms-core: ^10.4
* in2code/powermail: ^8
* tmw/fpdm: ^2.9

## Documentation

A more detailed [Documentation for PowermailPdf][1] can be found on the TYPO3 Documentation Server.

More information on the technical background can be found on the [blog post][2] (in German)


## Installation

1. Install the extension via [Composer][3]
2. Enable the extension in the TYPO3 extension manager
3. Add the static TypoScript
4. Enable the pdf creation for powermail by adding to your TypoScript constants:
   ```plugin.tx_powermailpdf.settings.enablePowermailPdf = 1```

**Important:** If you have multiple powermail form, but only one form should create a PDF – Add this constant only on the page with the powermail form plugin.

---

## Release Management

PowermailPdf uses [**semantic versioning**][2].


## Credits

The extension is created and maintained by [undkonsorten - Die Berliner Internetagentur][10] our developer [Eike Starkmann][9].

It was first published in 2013.


[1]: https://docs.typo3.org/p/undkonsorten/powermailpdf/main/en-us/
[2]: http://blog.undkonsorten.com/eigene-extension-vorgestellt-powermailpdf

[5]: https://semver.org/
[6]: https://getcomposer.org/

[9]: https://github.com/Starkmann/
[10]: https://undkonsorten.com/
