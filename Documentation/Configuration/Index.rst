.. include:: ../Includes.txt

.. _configuration:

=============
Configuration
=============


All configuration is done via TypoScript, besides the Label
“Download”. It can be found in *Resources/Private/Language/locallang.xml*

.. _configuration-typoscript:

TypoScript Reference
====================

Possible subsections: Reference of TypoScript options.

.. t3-field-list-table::
 :header-rows: 1

 - :Property:
         Property:

   :Data type:
         Data type:

   :Description:
         Description:

 - :Property:
         settings.filelink

   :Data type:
         filelink

   :Description:
         Defines the filelink which will be rendered in the thx view
         ::

            jumpurl = 1
            jumpurl.secure = 1
            jumpurl.secure.mimeTypes = pdf=application/pdf
            icon = 1
            icon\_link = 1

 - :Property:
         settings.fillPdf

   :Data type:
         boolean

   :Description:
          Fills the pdf form with powermail field values. If set to 0 otherwise the pdf is not filled but still can be downloaded or attached.

 - :Property:
         settings.fieldMap

   :Data type:
         array

   :Description:
         Maps the powermail form fields with the pdf field.
         ::

            fieldMap{
               #pdfField = PowermailField
               #firstname = namefirmaverein
               #lastname = e_mail
               name = vorname
               address = nachname
               city = email
               phone = email
            }

 - :Property:
         settings.enablePowermailPdf

   :Data type:
         boolean

   :Description:
          Activates/Deactivates the extension

 - :Property:
         settings.showDownloadLink

   :Data type:
         boolean

   :Description:
          Should the link be shown on the submit page?

 - :Property:
         settings.email.attachFile

   :Data type:
         boolean

   :Description:
           Attach the pdf to the mail? Remember there are a receiver and an sender mail, if you want to attach the pdf to one/both of
           this mails you also need to activate plugin.tx_powermail.settings.sender.attachment and/or plugin.tx_powermail.settings.receiver.attachment

 - :Property:
         settings.sourceFile

   :Data type:
         string

   :Description:
           Path of the pdf file



Example
====================
This is an example configuration.
   ::

      plugin.tx_powermailpdf {
         settings {
            filelink {
               jumpurl = 1
               jumpurl.secure = 1
               jumpurl.secure.mimeTypes = pdf=application/pdf
               icon = 1
               icon_link = 1
            }
            sourceFile = fileadmin/form.pdf
            fieldMap{
               #pdfField = PowermailField
               firstname = name
               lastname = lastname
               email = email
            }
         showDownloadLink = {$plugin.tx_powermailpdf.settings.showDownloadLink}
         email{
            attachFile = {$plugin.tx_powermailpdf.settings.email.attachFile}
         }
       }
     }