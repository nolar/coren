BEGIN {c=0;s=0; min=99999; max=0; }
#{ if ($1<0.050) { c++; s+=$1; } }
{ c++; s+=$1; if($1<min) min=$1; if($1>max) max=$1;}
END {print s/c; print min; print max;}