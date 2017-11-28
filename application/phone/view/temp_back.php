<?php
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" style="width:100%;color:#5cb85c">
<!-- 基本只和X轴有关，Y轴相对固定 -->
<!-- 前后端的异常值 -->
<line x1="{$B['Qmin']}%" y1="{$B['Y']+5}%" x2="{$B['Qmin']}%" y2="{$B['Y']+$B['box_heigh']-5}%"  style="stroke-width:3;stroke:#5cb85c; stroke-dasharray: 5;stroke-opacity:1"/>
<line x1="{$B['Qmin']}%" y1="{$B['Y']+$B['box_heigh']/2}%" x2="{$B['Q1']}%" y2="{$B['Y']+$B['box_heigh']/2}%"  style="stroke-width:3;stroke:#5cb85c; stroke-dasharray: 5;stroke-opacity:1"/>
<line x1="{$B['Qmax']}%" y1="{$B['Y']+5}%" x2="{$B['Qmax']}%" y2="{$B['Y']+$B['box_heigh']-5}%"  style="stroke-width:3;stroke:#5cb85c;stroke-dasharray: 5;stroke-opacity:1"/>
<line x1="{$B['Q3']}%" y1="{$B['Y']+$B['box_heigh']/2}%" x2="{$B['Qmax']}%" y2="{$B['Y']+$B['box_heigh']/2}%"  style="stroke-width:3;stroke:#5cb85c; stroke-dasharray: 5;stroke-opacity:1"/>
<!-- 盒子 -->
<rect x="{$B['Q1']}%" y="{$B['Y']}%" width="{$B['Q3']-$B['Q1']}%" height="{$B['box_heigh']}%" style="fill:none;stroke:#5cb85c;stroke-width:3;fill-opacity:0;stroke-opacity:0.9" />
<!-- 前须 -->
<line x1="{$B['Q0']}%" y1="{$B['Y']}%" x2="{$B['Q0']}%" y2="{$B['Y']+$B['box_heigh']}%"  style="stroke-width:3;stroke:#5cb85c;"/>
<line x1="{$B['Q0']}%" y1="{$B['Y']+$B['box_heigh']/2}%" x2="{$B['Q1']}%" y2="{$B['Y']+$B['box_heigh']/2}%"  style="stroke-width:3;stroke:#5cb85c;"/>
<!-- 后须 -->
<line x1="{$B['Q3']}%" y1="{$B['Y']+$B['box_heigh']/2}%" x2="99%" y2="{$B['Y']+$B['box_heigh']/2}%"  style="stroke-width:3;stroke:#5cb85c;"/>
<line x1="{$B['Q4']}%" y1="{$B['Y']}%" x2="{$B['Q4']}%" y2="{$B['Y']+$B['box_heigh']}%"  style="stroke-width:3;stroke:#5cb85c;"/>
<!-- 中位数 -->
<line x1="{$B['Q2']}%" y1="{$B['Y']}%" x2="{$B['Q2']}%" y2="{$B['Y']+$B['box_heigh']}%"  style="stroke-width:3;stroke:#5cb85c;"/>
<text x="{$B['Qmin']}%" y="{$B['Y']-5}%" fill="#5cb85c">{$B['min']}</text>
<text x="{$B['Q0']}%" y="{$B['Y']-5}%" fill="#5cb85c">{$B['Q0v']}</text>
<text x="{$B['Q1']}%" y="{$B['Y']-5}%" fill="#5cb85c">{$B['v25']}</text>
<text x="{$B['Q2']}%" y="{$B['Y']-5}%" fill="#5cb85c">{$B['median']}</text>
<text x="{$B['Q3']}%" y="{$B['Y']-5}%" fill="#5cb85c">{$B['v75']}</text>
<text x="{$B['Q4']-3}%" y="{$B['Y']-5}%" fill="#5cb85c" text-anchor="middle">{$B['Q4v']}</text>
<text x="{$B['Qmax']-3}%" y="{$B['Y']-5}%" fill="#5cb85c" text-anchor="middle">{$B['max']}</text>
</svg>