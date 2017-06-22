

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Known problems
--------------

Can only fill checkboxes in pdf file when pdftk is installed

If your template PDF is not compatible with this script, you can
process it with pdftk this way:

pdftk modele.pdf output modele2.pdf

Then try again with modele2.pdf.

The pdf links generated in mail may not work properly. This feature is
still buggy.


