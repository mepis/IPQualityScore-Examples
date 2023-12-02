import DeviceFingerprint from 'node_js_ipqs_device_tracker' import React from 'react';
import { useEffect } from 'react';

export const DeviceFingerPrintProvider = ({ children }) => {
    const user = { name: "Some Random User", userID: "12345" };
    useEffect(() => {
        if (user?.userID) {
            const key = '';
            DeviceFingerprint.initializeScriptAsync(key).then(() => {
                DeviceFingerprint.Store('userID', user.userID);
                DeviceFingerprint.AfterResult((result: any) => {
                    // Do something with result here
                    console.log('AfterResult')
                });
                DeviceFingerprint.AfterFailure(() => {
                    console.log('AfterFailure')
                });
                DeviceFingerprint.Init();
            }).catch((e) => {
                console.log(e)
            });
        }
    });
}