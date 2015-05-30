	program test_population
		use omp_lib
		integer*8 :: count, count_rate, count_max
		real*8 :: n
		integer*8 :: i, c
		integer*8, dimension(1:100000000) :: numbers
		integer*8, dimension(1:100000000) :: xornums

		print *, 'threads:', omp_get_max_threads()
		call system_clock(count, count_rate, count_max)
		print *, count, count_rate, count_max

		do i = 1,100000000
			call random_number(n)
			numbers(i) = n * huge(0_8)
		end do

		call system_clock(count, count_rate, count_max)
		print *, count, count_rate, count_max

	!$OMP PARALLEL DO PRIVATE(i,c)		
		do i = 1,100000000
			xornums(i) = xor(numbers(i), 123456789)
			c = popcnt(xornums(i))
			if (c < 15) then
			!	print *, numbers(i), xornums(i), c
			end if
		end do
	!$OMP END PARALLEL DO

 		call system_clock(count, count_rate, count_max)
		print *, count, count_rate, count_max
	
	end program test_population
