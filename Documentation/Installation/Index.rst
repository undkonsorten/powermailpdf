.. include:: ../Includes.txt

.. _installation:

Installation
============

- Install the extension via Composer (only)
- Enable the extension in the TYPO3 extension manager
- Add the static TypoScript
- Enable the pdf creation for powermail by adding to your TypoScript constants:

.. code-block:: typoscript

	plugin.tx_powermailpdf.settings.enablePowermailPdf = 1

.. Important::

	**Important:** If you have multiple powermail form, but only one form should create a PDF – Add this constant only on the page with the powermail form plugin.
