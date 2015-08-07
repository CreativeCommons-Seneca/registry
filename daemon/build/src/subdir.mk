################################################################################
# Automatically-generated file. Do not edit!
################################################################################

# Add inputs and outputs from these tool invocations to the build variables 
CPP_SRCS += \
../src/config.cpp \
../src/database.cpp \
../src/error.cpp \
../src/hashMatcher.cpp \
../src/regdaemon.cpp 

OBJS += \
./src/config.o \
./src/database.o \
./src/error.o \
./src/hashMatcher.o \
./src/regdaemon.o 

CPP_DEPS += \
./src/config.d \
./src/database.d \
./src/error.d \
./src/hashMatcher.d \
./src/regdaemon.d 


# Each subdirectory must supply rules for building sources it contributes
src/%.o: ../src/%.cpp
	@echo 'Building file: $<'
	@echo 'Invoking: Cross G++ Compiler'
	g++ -O3 -Wall -c -fmessage-length=0 -std=c++0x -Wall -MMD -MP -MF"$(@:%.o=%.d)" -MT"$(@:%.o=%.d)" -o "$@" "$<"
	@echo 'Finished building: $<'
	@echo ' '


