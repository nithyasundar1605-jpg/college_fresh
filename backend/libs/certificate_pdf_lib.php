<?php
require('fpdf.php');

class PDF_Certificate extends FPDF {
    function Sector($nCx, $nCy, $nR, $nStart, $nEnd, $style='FD', $cw=true, $o=90) {
        if($cw) {
            $tmp = $nStart;
            $nStart = $nEnd;
            $nEnd = $tmp;
        }
        $nStart += $o;
        $nEnd += $o;
        $nStart *= M_PI/180;
        $nEnd *= M_PI/180;
        if($nStart > $nEnd) $nEnd += 2*M_PI;
        $nDelta = $nEnd - $nStart;
        if($nDelta > M_PI) {
            $this->Sector($nCx, $nCy, $nR, $nStart*180/M_PI-$o, ($nStart+M_PI)*180/M_PI-$o, $style, !$cw, $o);
            $this->Sector($nCx, $nCy, $nR, ($nStart+M_PI)*180/M_PI-$o, $nEnd*180/M_PI-$o, $style, !$cw, $o);
            return;
        }
        $nMy = (4/3) * tan($nDelta/4);
        $this->_out(sprintf('%.2F %.2F m',($nCx+$nR*cos($nStart))*$this->k,($this->h-($nCy+$nR*sin($nStart)))*$this->k));
        $this->_Arc($nCx+$nR*cos($nStart), $nCy+$nR*sin($nStart), $nCx+$nR*(cos($nStart)-$nMy*sin($nStart)), $nCy+$nR*(sin($nStart)+$nMy*cos($nStart)), $nCx+$nR*(cos($nEnd)+$nMy*sin($nEnd)), $nCy+$nR*(sin($nEnd)-$nMy*cos($nEnd)), $nCx+$nR*cos($nEnd), $nCy+$nR*sin($nEnd));
        $this->_out(sprintf('%.2F %.2F l %s', $nCx*$this->k, ($this->h-$nCy)*$this->k, strtoupper($style)));
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4) {
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x2*$this->k, ($this->h-$y2)*$this->k, $x3*$this->k, ($this->h-$y3)*$this->k, $x4*$this->k, ($this->h-$y4)*$this->k));
    }

    function HeaderDesign() {
        // Gold/Blue Border Frame
        $this->SetDrawColor(200, 160, 0); // Gold
        $this->SetLineWidth(3);
        $this->Rect(20, 20, 1160, 810, 'D'); // Outer thick
        
        $this->SetDrawColor(0, 51, 102); // Dark Blue
        $this->SetLineWidth(1.5);
        $this->Rect(30, 30, 1140, 790, 'D'); // Inner thin

        // Corner Decorations
        $this->SetFillColor(0, 51, 102); // Dark Blue
        $this->Rect(30, 30, 80, 5, 'F'); // Top Left H
        $this->Rect(30, 30, 5, 80, 'F'); // Top Left V
        
        $this->Rect(1090, 30, 80, 5, 'F'); // Top Right H
        $this->Rect(1165, 30, 5, 80, 'F'); // Top Right V

        $this->Rect(30, 815, 80, 5, 'F'); // Bottom Left H
        $this->Rect(30, 740, 5, 80, 'F'); // Bottom Left V

        $this->Rect(1090, 815, 80, 5, 'F'); // Bottom Right H
        $this->Rect(1165, 740, 5, 80, 'F'); // Bottom Right V
    }

    function FooterDesign() {
        // Simple Seal Representation
        $this->SetDrawColor(200, 160, 0); // Gold
        $this->SetLineWidth(2);
        
        // Circular Seal Base
        $this->SetFillColor(255, 255, 255);
        // Using circle approximation since Sector is available or we can use regular circles if FPDF has them (it doesn't by default, but we can use multiple sectors)
        
        // Let's just draw a nice decorative line at bottom
        $this->SetDrawColor(180, 180, 180);
        $this->Line(200, 750, 1000, 750);
    }
}
?>
