<ul class="hfeed">
    <li class="hentry" tal:repeat="article net_nemein_news/news">
        <span tal:content="midcomshortdate: article/metadata/published" class="published">2007-08-01</span>
        <a href="#" tal:attributes="href article/url" tal:content="article/title" class="entry-title" rel="bookmark">Headline</a>
    </li>
</ul>

<p tal:content="structure net_nemein_news/previousnext">More</p>