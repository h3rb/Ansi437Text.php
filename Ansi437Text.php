<?php 
/*
 Ansi437Text.php
 ---------------------------------
 Provides support for text effects
 driven by ANSI Terminal Codes
 Support for IBM Code Page 437
 No support for interactivity.
 
Copyright (C) 2014  H. Elwood Gilliland III

This software is provided 'as-is', without any express or implied
warranty.  In no event will the author be held liable for any damages
arising from the use of this software.

Permission is granted to anyone to use this software for any purpose,
including commercial applications, and to alter it and redistribute it
freely, subject to the following restrictions:

1. The origin of this software must not be misrepresented; you must not
   claim that you wrote the original software. If you use this software
   in a product, an acknowledgement in the product documentation would be
   appreciated but is not required.
2. Altered source versions must be plainly marked as such, and must not be
   misrepresented as being the original software.
3. This notice may not be removed or altered from any source distribution.

 H. Elwood Gilliland III
 herb.gilliland@gmail.com
 
 */
 
 

global $t_screen;
$t_screen=t_screen_array();

// AnsiChar f and b:
// '0' black
// '1' red
// '2' green
// '3' brown
// '4' indigo
// '5' magenta
// '6' cyan
// '7' grey
//

class AnsiChar {
 var $f,$b,$c,$bold,$blink,$reverse,$transparent;
 function __construct( $f=0, $b=0, $c=' ', $bold=false, $blink=false, $reverse=false, $transparent=' ' ) {
  $this->f=$f;
  $this->b=$b;
  $this->c=$c;
  $this->bold=$bold;
  $this->blink=$blink;
  $this->reverse=$reverse;
  $this->transparent=$transparent;
 }
 function Fore( $a ) { if ( $a >= 0 ) $this->f=$a; }
 function Back( $b ) { if ( $b >= 0 ) $this->b=$b; }
 function Bold()   { $this->bold=true; }
 function Unbold() { $this->bold=false; }
 function Reverse() { $this->reverse=true; }
 function Unreverse() { $this->reverse=false; }
 function Blink() { $this->blink=true; }
 function Unblink() { $this->blink=false; }
 function Normal() { $this->bold=false; $this->blink=false; $this->reverse=false; }
 function Transparent() { $this->transparent=true; }
 function Opaque() { $this->transparent=false; }
 function Clear() {
  $f=0;
  $b=0;
  $c=' ';
  $bold=false;
  $blink=false;
  $reverse=false;
 }
 function GetCode() {
  $out='0;'.($this->f+30).';'.($this->b+40);
  if ( $this->bold    ) $out.=';1';
  if ( $this->reverse ) $out.=';7';
  if ( $this->blink   ) $out.=';5';
  return chr(27) . '[' . $out . 'm';
 }
};

function t_screen_array( $x=78, $y=24 ) {
 $screen=array();
 for ( $i=0; $i<$x; $i++ ) {
  $screen[$i]=array();
  for ( $j=0; $j<$y; $j++ ) {
   $screen[$i][$j]=new AnsiChar(0,0,$c);  
  }
 }
 return $screen;
}
 
function t_showans( $name ) { return file_get_contents($_GET['cwd'].'/data/winux/assets/'.$name.'.ans'); }
 
function t_savecurpos() { return chr(27) . '[s'; }
function t_loadcurpos() { return chr(27) . '[u'; }
 
function t_linewrap() { return chr(27) . '[=7h'; }

function t_nolinewrap() { return chr(27) . '[=71'; }

function t_up( $x=1 ) { return chr(27) . '[' . $x . 'A'; }
function t_down($x=1 ) { return chr(27) . '[' . $x . 'B'; }
function t_left( $x=1 ) { return chr(27) . '[' . $x . 'C'; }
function t_right($x=1 ) { return chr(27) . '[' . $x . 'D'; }

global $no_clearscreen,$no_cleareol;

$no_clearscreen = false;
$no_cleareol = false;

function t_clrscr( $x=1 ) {
 global $no_clearscreen; if ( $no_clearscreen ) return ''; else return chr(27) . '[2J';
}
function t_clreol() { 
 global $no_cleareol; if ( $no_cleareol ) return chr(27).'[K';
}

// Replaces a line with emptiness and resets to start of line
function t_clear_line() {
 echo "\r";
 echo t_clreol();
}

function t_color( $color ) {
 if ( strlen($color) == 0 ) return chr(27).'[m';
 $out = ""; $f = ""; $b = "";
 switch ( $color[0] ) { // foreground
  case '0': $f='0;30'; break; // black
  case 'r': $f='0;31'; break; // red
  case 'g': $f='0;32'; break; // green
  case 'y': $f='0;33'; break; // brown
  case 'b': $f='0;34'; break; // indigo
  case 'm': $f='0;35'; break; // magenta
  case 'c': $f='0;36'; break; // cyan
  case 'w': $f='0;37'; break; // grey
  case '1': $f='0;1;30'; break; // dark grey
  case 'R': $f='0;1;31'; break; // bright red
  case 'G': $f='0;1;32'; break; // full green
  case 'Y': $f='0;1;33'; break; // yellow
  case 'B': $f='0;1;34'; break; // bright blue
  case 'M': $f='0;1;35'; break; // bright purple
  case 'C': $f='0;1;36'; break; // full cyan
  case 'W': $f='0;1;37'; break; // white
  case '<': $b='7'; break; // inverse / reverse
  case 'X': $b='5'; break; // blink
 }
 if ( strlen($color) > 1 )
 switch ( $color[0] ) { // background
  case '0': $b='40'; break; // black
  case 'r': $b='41'; break; // red
  case 'g': $b='42'; break; // green
  case 'y': $b='43'; break; // brown
  case 'b': $b='44'; break; // indigo
  case 'm': $b='45'; break; // magenta
  case 'c': $b='46'; break; // cyan
  case 'w': $b='47'; break; // grey
  case '<': $b='7'; break; // inverse / reverse
  case 'X': $b='5'; break; // blink
 }
 $has_f=strlen($f) > 0;
 $has_b=strlen($b) > 0;
 return chr(27).'['.($has_f && $has_b ? $f.';'.$b : $has_f ? $f : $has_b ? $b : '' ).'m';
}

function t_color_mode($text, $status) {
 $out = "";
 switch($status) {
  case "SUCCESS":
   $out = "[42m"; //Green background
   break;
  case "FAILURE":
   $out = "[41m"; //Red background
   break;
  case "WARNING":
   $out = "[43m"; //Yellow background
   break;
  case "NOTE":
   $out = "[44m"; //Blue background
   break;
  default:
   throw new Exception("Invalid status: " . $status);
 }
 return chr(27) . "$out" . "$text" . chr(27) . "[0m";
}


class AnsiScreen {
 var $w,$h,$screen;
 function __construct( $x=78, $y=24 ) {
  $this->w=$x;
  $this->h=$y;
  $this->screen = t_screen_array($x,$y);
 }
 function Clear() { $this->screen = t_screen_array($this->w,$this->h);}
 function Chr( $x, $y, $ac ) { $this->screen[$x][$y]=chr($ac); }
 function Char( $x, $y, $c ) { $this->screen[$x][$y]=$c; }
 function Color( $x, $y, $f=0, $b=0 ) {
  $this->screen[$x][$y]->Fore($f);
  $this->screen[$x][$y]->Back($b);
 }
 function A( $f=0, $b=0, $c=' ', $bold=false, $blink=false, $reverse=false, $transparent=' ' ) {
  return new AnsiChar( $f, $b, $c, $bold, $blink, $reverse, $transparent );
 }
 function Text( $s, $x, $y, $attrib ) {
  $lines=explode("\n",$s);
  $j=$y;
  foreach ( $lines as $line ) {
   $len=strlen($line);
   if ( $len == 0 ) $j++;
   else {
    $i=$x;
	$L=str_split($line);
	foreach ( $L as $c ) {
	 $this->screen[$i][$j]=$attrib;
	 $this->screen[$i][$j]->c=$c;
	 $i++;
	}
	$j++;
   }
  }
 }
 function Area( $x, $y, $w=1, $h=1, $c=' ', $f=0, $b=0 ) {
  for ( $i=$x; $i<$x+$w; $i++ ) {
   for ( $j=$y; $j<$y+$h; $j++ ) {
    $this->screen[$i][$j]->Color($f,$b);
	$this->screen[$i][$j]->Char($c);
   }
  }
 }
 function AreaCode( $x, $y, $w=1, $h=1, $ac=32, $f=0, $b=0 ) {
  for ( $i=$x; $i<$x+$w; $i++ ) {
   for ( $j=$y; $j<$y+$h; $j++ ) {
    $this->screen[$i][$j]->Color($f,$b);
	$this->screen[$i][$j]->Chr($ac);
   }
  }
 }
 function Rectangle(
   $fillAnsiChar, $x, $y, $w=1, $h=1, 
   $borderAttrib, // another AnsiChar class that sets border attributes
   $T='-', $B='-', $L='|', $R='|',
   $TL='+', $TR='+', $BL='+', $BR='+' ) {
  for ( $i=$x; $i<$x+$this->w; $i++ ) {
   for ( $j=$y; $j<$y+$this->h; $j++ ) {
    if ( $i == $x && $j == $y ) { // top left
	 $this->screen[$x][$y]=$borderAttrib;
	 $this->screen[$x][$y]->c=$TL;
	} else
    if ( $i == $x+$this->w-1 && $j == $y ) { // top right
	 $this->screen[$x][$y]=$borderAttrib;
	 $this->screen[$x][$y]->c=$TR;
	} else
    if ( $i == $x && $j == $y+$this->h-1 ) { // bottom left
	 $this->screen[$x][$y]=$borderAttrib;
	 $this->screen[$x][$y]->c=$BL;
	} else
    if ( $i == $x+$this->w-1 && $j == $y+$this->h-1 ) { // bottom right
	 $this->screen[$x][$y]=$borderAttrib;
	 $this->screen[$x][$y]->c=$BR;
	} else
    if ( $j == $y ) { // top
	 $this->screen[$x][$y]=$borderAttrib;
	 $this->screen[$x][$y]->c=$T;
	} else
    if ( $j == $y+$this->h-1 ) { // bottom
	 $this->screen[$x][$y]=$borderAttrib;
	 $this->screen[$x][$y]->c=$B;
	} else
    if ( $i == $x ) { // left
	 $this->screen[$x][$y]=$borderAttrib;
	 $this->screen[$x][$y]->c=$L;
	} else
    if ( $i == $x+$this->w-1 ) { // right
	 $this->screen[$x][$y]=$borderAttrib;
	 $this->screen[$x][$y]->c=$R;
	} else { // fill 
	 $this->screen[$x][$y]=$fillAnsiChar;
	} 
   }
  }
 }
 function Box1( $fillAnsiChar, $x, $y, $w=1, $h=1, $borderAttrib ) {
  Rectangle( $fillAnsiChar, $x, $y, $w, $h, $borderAttrib,
   chr(196), chr(196), chr(179), chr(179),
   chr(218), chr(191), chr(192), chr(217)
  );
 }
 function Box2( $fillAnsiChar, $x, $y, $w=1, $h=1, $borderAttrib ) {
  Rectangle( $fillAnsiChar, $x, $y, $w, $h, $borderAttrib,
   chr(205), chr(205), chr(186), chr(186),
   chr(201), chr(187), chr(200), chr(188)
  );
 }
 function Box12( $fillAnsiChar, $x, $y, $w=1, $h=1, $borderAttrib ) {
  Rectangle( $fillAnsiChar, $x, $y, $w, $h, $borderAttrib,
   chr(196), chr(196), chr(186), chr(186),
   chr(214), chr(183), chr(211), chr(189)
  );
 }
 function Box21( $fillAnsiChar, $x, $y, $w=1, $h=1, $borderAttrib ) {
  Rectangle( $fillAnsiChar, $x, $y, $w, $h, $borderAttrib,
   chr(205), chr(205), chr(179), chr(179),
   chr(213), chr(184), chr(212), chr(190)
  );
 }
 function Bold( $x, $y, $w, $h ) {
  for ( $i=$x; $i<$x+$w; $i++ ) {
   for ( $j=$y; $j<$y+$h; $j++ ) {
    $this->screen[$i][$j]->Bold();
   }
  }
 }
 function Blink( $x, $y, $w, $h ) {
  for ( $i=$x; $i<$x+$w; $i++ ) {
   for ( $j=$y; $j<$y+$h; $j++ ) {
    $this->screen[$i][$j]->Blink();
   }
  }
 }
 function Reverse( $x, $y, $w, $h ) {
  for ( $i=$x; $i<$x+$w; $i++ ) {
   for ( $j=$y; $j<$y+$h; $j++ ) {
    $this->screen[$i][$j]->Reverse();
   }
  }
 }
 function Normal( $x, $y, $w, $h ) {
  for ( $i=$x; $i<$x+$w; $i++ ) {
   for ( $j=$y; $j<$y+$h; $j++ ) {
    $this->screen[$i][$j]->Normal();
   }
  }
 }
 function GetPixel( $x, $y ) { return $this->screen[$x][$y]; }
 function CopyPixel( &$out, $x, $y ) { $out=$this->screen[$x][$y]; }
 function toString() {
  $out="";
  $last="";
  for ( $y=0; $y<$this->h; $y++ ) {
   for ( $x=0; $x<$this->w; $x++ ) {
    $pixel=$this->screen[$x][$y];
    $code=$pixel->GetCode();
	if ( $code != $last ) {
	 $out.=$code;
  	 $last=$code;
	}
	$out.=$pixel->c;
   }
   $out.="\n";
  }
 }
 function Show() {
  echo t_clrscr() . $this->toString();
 }
 function Copy( $from, $sx, $sy, $sw, $sh, $dx, $dy ) {
  for ( $i=0; $i<$sw; $i++ ) {
   for ( $j=0; $j<$sh; $j++ ) {
    if ( isset($this->screen[$dx+$i][$dy+$j])
      && isset($from->screen[$sx+$i][$sy+$j]) )
	 $this->screen[$dx+$i][$dy+$j]=$from->screen[$sx+$i][$sy+$j];
   }
  }
 }
 function Import( $asset, $x=0, $y=0 ) { // Imports an ANS file
  $asset=t_showans($asset);
  $asset=str_split($asset);
  $i=0;
  // Run through asset, parsing ansi codes...
 }
};

global $ansi;
$ansi=new AnsiScreen();

global $fb;
$fb=array();

for ( $i=0; $i<10; $i++ ) $fb[$i]=new AnsiScreen();

?>
