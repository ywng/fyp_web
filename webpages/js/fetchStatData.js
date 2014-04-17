/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
 
 /**
 * Author: NG, Yik-wai Jason
 * Contact & Support: ywng@ust.hk
 * The Hong Kong University of Science and Technology
 * Data Visualization, CSE, HKUST
 */

var DayHourHeatmap=new Array();
var WeekBarChart=new Array();

function _init(){
	// fetch data from database
	//should have some sort of API for getting the data
	$.ajax({
		url: serverDomain+"index.php/statistics/getAllOrderHourWeek",
		context: document.body,
		dataType: "json", 
		headers : {Accept : "application/json","Access-Control-Allow-Origin" : "*"},
		type: 'GET', 
		async: false,
		success: function(data, textStatus, jqXHR){
			var resultArray=data.result;
			for(var i=0;i<resultArray.length;i++){
				var dataPt=new Array();
				dataPt[0]=resultArray[i].weekay;
				dataPt[1]=resultArray[i].hour;
				dataPt[2]=resultArray[i].freq;

				DayHourHeatmap[i]=dataPt;
			}

			/*for debug only**
			for(var i=0;i<resultArray.length;i++){
				console.log(DayHourHeatmap[i][0]+ "  "+DayHourHeatmap[i][1]+"  "+DayHourHeatmap[i][2]);
			}*/
		},
		error: function(jqHXR, textStatus, errorThrown) {
			console.log('ajax error in get survey ID call:' +textStatus + ' ' + errorThrown);
		}

	 }); // end of the ajax call

	$.ajax({
		url: serverDomain+"index.php/statistics/getAllOrderWeekDay",
		context: document.body,
		dataType: "json", 
		headers : {Accept : "application/json","Access-Control-Allow-Origin" : "*"},
		type: 'GET', 
		async: false,
		success: function(data, textStatus, jqXHR){
			WeekBarChart=data.result;	
		},
		error: function(jqHXR, textStatus, errorThrown) {
			console.log('ajax error in get survey ID call:' +textStatus + ' ' + errorThrown);
		}

	 }); // end of the ajax call

	

}

