# PHPChess

For fun and for programming practice, I created a chess move generator and web chess GUI. I did it in my most comfortable language (PHP), without reading any literature on how to structure the program.

I ended up using lots of OOP. I created the following classes: ChessRulebook (static), ChessMove, ChessBoard, ChessPiece, ChessSquare.

I have about 2000 lines of code so far. Most of the interesting code is in the ***models*** folder.

I believe my move generation code to be bug free. I've tested about 30 perfts to depth 3 and everything passes.

## Links

- Compiled Code - https://www.reddragonwebdesign.com/projects/php-chess
- Benchmark (Perft Depth 2) - https://www.reddragonwebdesign.com/projects/php-chess/perft.php?fen=r1bqk2r%2Fppp2ppp%2F2np1n2%2F2b1p3%2F2B1P3%2F2PP1N2%2FPP3PPP%2FRNBQ1RK1+b+kq+-+1+6
- Code Review (Stack Exchange) - https://codereview.stackexchange.com/questions/203324/php-chess-version-2
- TalkChess.com - http://talkchess.com/forum3/viewtopic.php?f=7&t=68470

## Speed

My code is fast enough for a website that plays chess. However it is too slow to be a chess AI. It takes 4ms to generate all legal moves for one position.

I am currently trying to optimize the code without gutting it. I have learned a lot about code speed/optimization in the process. Here are my tips:

- Server Side
  - Use the latest version of PHP. PHP 7 is twice as fast as PHP 5.
  - Turn on OpCache
  - Disable XDebug - 9x faster
- PHP
  - Prefer constants over variables
  - Prefer integers over strings
  - Keep class variables lean. Don't calculate extra variables in the constructor. Use getters for those. (e.g. don't have a FEN variable in ChessBoard)
  - Prefer $haystack[needle] over array_search($needle, $haystack)
  - Use XDEBUG_PROFILE and Qcachegrind. Sort by SELF. Optimize the functions at the top.
  - Extract code groups into functions to help with profiling (and readability).
  - Don't create functions/classes that can be done with php:internal functions (e.g. Dictionary class)
  - Prefer $array[] = $push over array_push($array, $push)
  
This website doesn't use SQL, but here is an SQL bonus tip I learned from optimizing another website:

- SQL
  - Do not put SQL queries in loops. Use JOINS and SUBQUERIES instead. (the per query travel time to the MySQL server is killer)