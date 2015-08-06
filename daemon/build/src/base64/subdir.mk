################################################################################
# Automatically-generated file. Do not edit!
################################################################################

# Add inputs and outputs from these tool invocations to the build variables 
C_SRCS += \
../src/base64/cdecode.c \
../src/base64/cencode.c 

OBJS += \
./src/base64/cdecode.o \
./src/base64/cencode.o 

C_DEPS += \
./src/base64/cdecode.d \
./src/base64/cencode.d 


# Each subdirectory must supply rules for building sources it contributes
src/base64/%.o: ../src/base64/%.c
	@echo 'Building file: $<'
	@echo 'Invoking: Cross GCC Compiler'
	gcc -O3 -Wall -c -fmessage-length=0 -MMD -MP -MF"$(@:%.o=%.d)" -MT"$(@:%.o=%.d)" -o "$@" "$<"
	@echo 'Finished building: $<'
	@echo ' '


