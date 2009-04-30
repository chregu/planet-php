if test ! $TESTS -o -z $TESTS;then
export TESTS=$PWD/tests
fi

export TEST_PHP_EXECUTABLE4=`which php`
export TEST_PHP_EXECUTABLE5=`which php5`

if test -z $1; then
	VERSION=`cat ../VERSION`
else 
	VERSION=$1
fi

if test  $VERSION = '4'; then
    export TEST_PHP_EXECUTABLE=$TEST_PHP_EXECUTABLE4
    $TEST_PHP_EXECUTABLE -d 'output_buffering=0' run-tests.php4 $TESTS;
elif test $VERSION = '5'; then
    export TEST_PHP_EXECUTABLE=$TEST_PHP_EXECUTABLE5
    $TEST_PHP_EXECUTABLE -d 'output_buffering=0' run-tests.php5 $TESTS;
fi

