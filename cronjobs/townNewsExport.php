<?php
  //export to TN
  /* sample format
  
   <?xml version="1.0" encoding="utf-8"?>
 <!DOCTYPE nitf PUBLIC "-//IPTC-NAA//DTD NITF 3.1//EN"
                      "http://www.nitf.org/site/nitf-documentation/nitf-3-1.dtd">

 <nitf>
 <head>
  <docdata management-status="embargoed">

   <!-- The unique ID of this document -->

   <doc-id id-string="iq_3509888" />

   <!-- Release date and time are used for determining when an article will appear active -->

   <date.release norm="20050504T000000"/>

   <!-- Expiration date and time are used for determining when an article will archive -->

   <date.expire norm="20080505T100230"/>

   <!-- Keywords are used for tagging articles -->

   <key-list>
    <keyword key="example" />
    <keyword key="embed" />
    <keyword key="another keyword" />
   </key-list>

  </docdata>

  <!-- The pubdata element contains the category and rank of the article -->

  <pubdata type="web" position.section="news" position.sequence="95"/>
 </head>
 <body>
  <body.head>

   <!-- hl1 is the main headline, hl2 is the subheadline -->

   <hedline>
    <hl1>Headline</hl1>
    <hl2>Subheadline</hl2>
   </hedline>

   <!-- The byline element is used for the author -->

   <byline>By John Doe</byline>
  </body.head>
  <body.content>

    <!-- Each unique paragraph MUST appear in <p> tags. If you use HTML
    elements like <b> and <i>, you must further wrap the text of the
    paragraph in a CDATA section -->

    <p>This is my favorite article about examples</p>
    <p><![CDATA[This is how to embed <b>bold</b>]]></p>

    <!-- The media element is used to add images with captions.
    The order of media elements determines the order in the document -->

    <media media-type="image">
     <media-reference source="example.jpg" />
     <media-caption>This is the cutline</media-caption>
    </media>
  </body.content>
 </body>
 </nitf>

 
 
 AUDIO FILE FORMAT
 <?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE nitf PUBLIC "-//IPTC-NAA//DTD NITF 3.1//EN"
"http://www.iptc.org/std/NITF/3.4/specification/dtd/nitf-3-4.dtd">

<nitf>
<head>
<docdata management-status="embargoed">
<doc-id id-string="real_estate_podcast"/>
<date.release norm="20091002T000000"/>
</docdata>
<pubdata type="web" position.section="podcasts/real_estate" position.sequence="0"/>
</head>
<body>
<body.head>
<hedline>
<hl1>October 2nd, 2009 Real Estate Podcast</hl1>
</hedline>
</body.head>
<body.content>
<media media-type="audio">
<media-reference source="real_estate.mp3" />
<media-caption>Real Estate and Financial expert Jane Doe talks about the current market.</media-caption>
<media-producer>Jane Doe</media-producer>
</media>
</body.content>
</body>
</nitf>

PHOTO GALLERY
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE nitf PUBLIC "-//IPTC-NAA//DTD NITF 3.1//EN"
"http://www.iptc.org/std/NITF/3.4/specification/dtd/nitf-3-4.dtd">

<nitf>
<head>
<meta name="blox-is-collection" content="1" />
<docdata management-status="embargoed">
<doc-id id-string="mycollection"/>
<date.release norm="20090925T000000"/>
</docdata>
<pubdata type="web" position.section="gallery/pets" position.sequence="0"/>
</head>
<body>
<body.head>
<hedline>
<hl1>Fuuny Cats</hl1>
</hedline>
</body.head>
<body.content>
<media media-type="image">
<media-reference source="bachelor-cat.jpg" />
<media-caption>This cat knows how to kick back and relax!</media-caption>
<media-producer>Cat Photographer</media-producer>
</media>
<media media-type="image">
<media-reference source="petting-zoo.jpg" />
<media-caption>This cat is petting a bird!</media-caption>
<media-producer>Cat Photographer</media-producer>
</media>
<media media-type="image">
<media-reference source="puss-in-boots.jpg" />
<media-caption>A couple of cats getting into trouble</media-caption>
<media-producer>Cat Photographer</media-producer>
</media>
<media media-type="image">
<media-reference source="relaxed-cat.jpg" />
<media-caption>This cat had a long day</media-caption>
<media-producer>Cat Photographer</media-producer>
</media>
</body.content>
</body>
</nitf>
  
  */
?>
