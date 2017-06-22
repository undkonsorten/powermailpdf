

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


Administration
--------------

If you want the download link to be shown on the thx site after the user submitted his data you need to edit the Web.html template (Resources/Private/PowermailAll/Web.html) of powermail.

Snippet
   ::
   
      <f:if condition="{0:answer.field.type} == {0:'downloadLink'}">
         <f:then>
            <f:format.html>{answer.value}</f:format.html>
         </f:then>
         <f:else>
            <f:format.nl2br>{answer.value}</f:format.nl2br>
         </f:else>
      </f:if>
      
Example:
   ::
   
      {namespace vh=In2code\Powermail\ViewHelpers}

      <f:comment>
         Mail: {mail}
         Answer: {answer}
      </f:comment>
      
      
      <dt class="powermail_all_label powermail_all_type_{answer.field.type} powermail_all_marker_{answer.field.marker}">
         <vh:string.RawAndRemoveXss>{answer.field.title}</vh:string.RawAndRemoveXss>
      </dt>
      <dd class="powermail_all_value powermail_all_type_{answer.field.type} powermail_all_marker_{answer.field.marker}">
         <f:if condition="{vh:Condition.IsArray(val: '{answer.value}')}">
            <f:else>
               <f:if condition="{0:answer.field.type} == {0:'downloadLink'}">
                  <f:then>
                     <f:format.html>{answer.value}</f:format.html>
                  </f:then>
                  <f:else>
                     <f:format.nl2br>{answer.value}</f:format.nl2br>
                  </f:else>
               </f:if>
            </f:else>
            <f:then>
               <f:for each="{answer.value}" as="subValue" iteration="index">
                  <f:if condition="{subValue}">
                     {subValue}<f:if condition="{index.isLast}"><f:else>, </f:else></f:if>
                  </f:if>
               </f:for>
            </f:then>
         </f:if>
      </dd>
      
The extension adds to new fields to powermail form: **donwloadLink** and **file**. If you don't want to have them on thethx site or in the email you need
so filter them in the Web.html and Mail.html template of powermail.

Can look like this:
   ::
      
      <f:if condition="{0:answer.field.type} != {0:'downloadLink'}">
            <f:format.nl2br>{answer.value}</f:format.nl2br>
      </f:if>

The extension uses FPDM from fpdftk
(http://www.fpdf.org/en/script/script93.php) which does not support
check boxes in pdfs.

If you want to fill checkboses in the pdf you need to have pdftk
(http://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/) installed on
your server. The the extension uses pdftk to create the pdf and
checkboxes can be filed. (may not work yet)