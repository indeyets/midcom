<ul>
  <li tal:repeat="article net_nemein_news/news">
      <span tal:content="midcomshortdate: article/metadata/published">2007-08-01</span>
      <a href="#" tal:attributes="href article/url" tal:content="article/title">Headline</a>
  </li>
</ul>

<p tal:content="structure net_nemein_news/previousnext">More</p>