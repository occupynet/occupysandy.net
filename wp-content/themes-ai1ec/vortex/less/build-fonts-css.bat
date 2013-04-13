@echo off
where /q lessc || (
    echo You must install lessc: npm install -g less
    goto :eof
)
lessc --yui-compress fonts\font-awesome.less ..\font_css\font-awesome.css
lessc --yui-compress fonts\font-league-gothic.less ..\font_css\font-league-gothic.css

