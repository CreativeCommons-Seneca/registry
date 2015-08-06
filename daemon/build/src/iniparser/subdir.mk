################################################################################
# Automatically-generated file. Do not edit!
################################################################################

# Add inputs and outputs from these tool invocations to the build variables 
C_SRCS += \
../src/iniparser/dictionary.c \
../src/iniparser/iniparser.c 

OBJS += \
./src/iniparser/dictionary.o \
./src/iniparser/iniparser.o 

C_DEPS += \
./src/iniparser/dictionary.d \
./src/iniparser/iniparser.d 


# Each subdirectory must supply rules for building sources it contributes
src/iniparser/%.o: ../src/iniparser/%.c
	@echo 'Building file: $<'
	@echo 'Invoking: Cross GCC Compiler'
	gcc -O3 -Wall -c -fmessage-length=0 -MMD -MP -MF"$(@:%.o=%.d)" -MT"$(@:%.o=%.d)" -o "$@" "$<"
	@echo 'Finished building: $<'
	@echo ' '


